<?php /** @noinspection DuplicatedCode */
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection PhpUnused */

/**
 * Class ElasticaManager
 * @package App\Bundles\Attribute
 *
 * since: 23.06.2022
 * author: alexej@kisselev.de
 */

namespace App\Bundles\Attribute\Manager;

use App\Bundles\Attribute\Entity\AttributableEntity;
use App\Entity\Attribute;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Elastica\Bulk;
use Elastica\Document;
use Elastica\Exception\ResponseException;
use Elastica\Query;
use Elastica\Query\BoolQuery;
use Exception;
use FOS\ElasticaBundle\Elastica\Index;
use FOS\ElasticaBundle\Index\IndexManager;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Security;
use Throwable;

class AttributeManagerNested extends AbstractAttributeManager
{
    protected ArrayCollection $documents;

    public function __construct(IndexManager $indexManager, Security $security, FlashBagInterface $flashBag, EntityManagerInterface $em)
    {
        parent::__construct($indexManager, $security, $flashBag, $em);
        $this->documents = new ArrayCollection();
    }

    // =============================== WORKFLOW METHODS  ===============================

    public function getAttributeValues(AttributableEntity $entity): array
    {
        $docId = $this->getDocumentId($entity);
        if ($this->attributeValues === null) {
            $this->attributeValues = [];
            // update attribute values. can be lazy loading
            $documents = $this->getDocuments();
            /** @var Document $document */
            foreach ($documents as $document) {
                $docData = $document->getData();
                $documentId = $document->getId();
                $attributes = $docData[self::ATTRIBUTE_FIELD] ?? [];
                foreach ($attributes as $attrData) {
                    $this->attributeValues[$documentId][$attrData['uniqueKey']] = $this->convertValue($attrData['uniqueKey'], $attrData[$attrData['type']] ?? null);
                }
            }
            // add attributes has no value

            if (!array_key_exists($docId, $this->attributeValues)) {
                $this->attributeValues[$docId] = [];
            }
            /** @var Attribute $attribute */
            foreach ($entity->getCategory()->getAttributes(true) as $attribute) {
                if (!array_key_exists($attribute->getUniqueKey(), $this->attributeValues[$docId])) {
                    $this->attributeValues[$docId][$attribute->getUniqueKey()] = null;
                }
            }
        }
        return $this->attributeValues[$docId] ?? [];
    }

    /**
     * Loads elastica documents.
     * Documents can be loaded subsequently for lazy loaded entities
     * @return ArrayCollection
     */
    protected function getDocuments(): ArrayCollection
    {
        if ($this->entities->isEmpty()) {
            return $this->documents;
        }
        $entities = new ArrayCollection();

        /** @var AttributableEntity $entity */
        foreach ($this->entities as $entity) {
            $key = $this->getDocumentId($entity);
            if (in_array($key, $this->initialisedEntities, true)) {
                continue;
            }
            $entities->add($entity);
            $this->initialisedEntities[] = $key;
        }
        if ($entities->isEmpty()) {
            return $this->documents;
        }
        // load documents from elasticsearch
        $query = $this->getQuery($entities);
        $query->setSize(9999);
        try {
            $documents = $this->getIndex()->search($query)->getDocuments();
            foreach ($documents as $document) {
                $this->documents->add($document);
            }
        } catch (Exception $e) {
            error_log($e);
            if ($e instanceof ResponseException) {
                // highly likely the index doesn't exist
                // create one
                $created = $this->createIndexIfNotExists();
                if ($created) {
                    // return empty collection because the index is new
                    return $this->documents;
                }
            }
        }
        return $this->documents;
    }

    protected function getQuery(Collection $entities): Query
    {
        $ids = [];
        $scopes = [];
        /** @var AttributableEntity $campaign */
        foreach ($entities as $entity) {
            try {
                $ids[] = $entity->getId();
                $scopes[] = $this->getScope($entity);
            } catch (Throwable $e) {
                // detached/removed entity
            }

        }
        $termsQuery = new Query\Terms('id', $ids);
        $queryBool = new BoolQuery();
        $queryBool->addMust($termsQuery);

        $termsQueryScope = new Query\Terms('scope', array_unique($scopes));
        $queryBool->addMust($termsQueryScope);

        $query = new Query();
        $query->setQuery($queryBool);
        return $query;
    }

    public function flush(): void
    {
        if ($this->entities->isEmpty()) {
            return;
        }
        $documents = [];
        //upsert entity documents

        /** @var AttributableEntity $entity */
        foreach ($this->entities as $entity) {
            $attributeValues = [];
            foreach ($entity->getAttributeValues() as $uniqueKey => $attributeValue) {
                $attribute = $this->getAttribute($uniqueKey);
                if ($attribute === null) {
                    continue;
                }
                if (empty($attributeValue)) {
                    continue;
                }
                $type = $attribute->getAttributeDefinition()->getType();
                $val = [
                    'id'        => $attribute->getId(),
                    'uniqueKey' => $uniqueKey,
                    'type'      => $type,
                    $type       => $this->convertValue($uniqueKey, $attributeValue, false)
                ];
                $attributeValues[] = $val;
            }
            $document = $this->getDocument($this->getScope($entity), $entity->getId());
            if ($document instanceof Document) {
                $docData = $document->getData();
                $docData[self::ATTRIBUTE_FIELD] = $attributeValues;
            } else {
                $docData = [
                    'id'                  => $entity->getId(),
                    'scope'               => $this->getScope($entity),
                    self::ATTRIBUTE_FIELD => $attributeValues
                ];
                $document = new Document($this->getDocumentId($entity), $docData, $this->getIndex());
            }
            $document->setData($docData);
            $documents[] = $document;
        }
        if (empty($documents)) {
            return;
        }

        foreach (array_chunk($documents, 500) as $docs) {
            $bulk = new Bulk($this->getIndex()->getClient());
            if (count($documents) < 10) {
                $bulk->setRequestParam('refresh', true);
            }
            $bulk->addDocuments($docs);
            try {
                $response = $bulk->send();
                if ($response->isOk()) {
                    $this->getFlashBag()->add('success', 'Attributes have been saved');
                }
            } catch (Exception $e) {
                $this->getFlashBag()->add('error', 'An error occurred during saving');
                $this->getFlashBag()->add('info', $e->getMessage());
            }
        }
        $this->entities->clear();
    }


    /**
     * @param string $scope
     * @param int $id
     * @return Document|null
     */
    public function getDocument(string $scope, int $id): ?Document
    {
        $result = $this->getDocuments()->filter(static function (Document $doc) use ($scope, $id) {
            // filter by unique key
            $docData = $doc->getData();
            if ($docData['scope'] === $scope && $docData['id'] === $id) {
                return $doc;
            }
            return null;
        });
        if ($result->isEmpty()) {
            return null;
        }
        return $result->first();
    }

    /**
     * Creates an elasticsearch index if it not exists
     * @return bool
     */
    protected function createIndexIfNotExists(): bool
    {
        if (!$this->getIndex()->exists()) {
            $this->getIndex()->create([
                'settings' => [
                    'number_of_shards'   => 1,
                    'number_of_replicas' => 0
                ],
                'mappings' => [
                    'properties' => [
                        self::ATTRIBUTE_FIELD => [
                            'type' => 'nested'
                        ],
                        'created_at'          => [
                            'type' => 'date'
                        ],
                        'updated_at'          => [
                            'type' => 'date'
                        ]
                    ]
                ],
            ]);
            return true;
        }
        return false;
    }

    protected function getIndex(): Index
    {
        return $this->getIndexManager()->getIndex('nested');
    }
}