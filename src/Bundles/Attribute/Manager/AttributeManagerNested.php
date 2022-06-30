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
use Doctrine\Common\Collections\Collection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use Elastica\Bulk;
use Elastica\Document;
use Elastica\Query;
use Elastica\Query\BoolQuery;
use Exception;
use FOS\ElasticaBundle\Elastica\Index;
use Throwable;

class AttributeManagerNested extends AbstractAttributeManager
{

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
                $docData = $document instanceof Document ? $document->getData() : $document;
                $documentId = $document instanceof Document ? $document->getId() : $this->getDocumentId(null, $docData);
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
        $documentsAll = $this->getDocuments(true);
        $documents = [];
        //upsert entity documents

        /** @var AttributableEntity $entity */
        foreach ($this->entities as $entity) {
            $attributeValues = [];
            $docId = $this->getDocumentId($entity);
            foreach ($entity->getAttributeValues() as $uniqueKey => $attributeValue) {
                $attribute = $this->getAttribute($uniqueKey);
                if ($attribute === null) {
                    continue;
                }
                if (empty($attributeValue)) {
                    continue;
                }
                $val = $entity->createDocData($attribute);
                $attributeValues[] = $val;
            }
            $document = $documentsAll[$docId] ?? null;
            if ($document instanceof Document) {
                $docData = $document->getData();
                $entity->updateDocData($docData);
                $docData[self::ATTRIBUTE_FIELD] = $attributeValues;
            } else {
                $docData = $entity->createDocData();
                $docData[self::ATTRIBUTE_FIELD] = $attributeValues;
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

    protected function getSearchQuery(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields): ?Query
    {
        return null;
    }
}