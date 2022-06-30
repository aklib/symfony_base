<?php /** @noinspection DuplicatedCode */

/**
 * Class AttributeManagerParentChild
 * @package App\Bundles\Attribute\Manager
 *
 * since: 29.06.2022
 * author: alexej@kisselev.de
 */

namespace App\Bundles\Attribute\Manager;

use App\Bundles\Attribute\Entity\AttributableEntity;
use App\Entity\Attribute;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Elastica\Bulk;
use Elastica\Document;
use Elastica\Query;
use Exception;
use FOS\ElasticaBundle\Elastica\Index;
use Throwable;

class AttributeManagerParentChild extends AbstractAttributeManager
{
    protected array $documents = [];

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
                $uniqueKey = $docData['uniqueKey'];
                $this->attributeValues[$this->getDocumentId(null, $docData)][$uniqueKey] = $this->convertValue($uniqueKey, $docData[$docData['type']] ?? null);
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
     * @return array
     */
    protected function getDocuments(): array
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
                $this->documents[$document->getId()] = $document;
            }
        } catch (Exception $e) {
            error_log($e);
            $created = $this->createIndexIfNotExists();
            if (!$created) {
                $this->getFlashBag()->add('warning', 'An error occurred during getting. ' . $e->getMessage());
            }
        }
        return $this->documents;
    }

    /**
     * @param Collection $entities
     * @return Query
     */
    protected function getQuery(Collection $entities): Query
    {
        $ids = [];
        /** @var AttributableEntity $campaign */
        foreach ($entities as $entity) {
            try {
                $ids[] = $this->getDocumentId($entity);
            } catch (Throwable $e) {
                // detached/removed entity
            }
        }
        $queryJoin = new Query();
        $queryJoin->setQuery(new Query\Terms('_id', $ids));
        $queryTree = new Query\HasParent($queryJoin, 'entity');
        $query = new Query();
        $query->setQuery($queryTree);
        return $query;
    }

    /** @noinspection NestedPositiveIfStatementsInspection */
    public function flush(): void
    {
        if ($this->entities->isEmpty()) {
            return;
        }
        $documents = [];
        //upsert entity documents

        /** @var AttributableEntity $entity */
        foreach ($this->entities as $entity) {
            $docId = $this->getDocumentId($entity);
            /** @var Document $document */
            $document = $this->documents[$docId] ?? null;
            if ($document instanceof Document) {
                $docData = $document->getData();
                if ($entity->updateDocData($docData)) {
                    $documents[] = $document;
                }
            } else {
                $docData = $entity->createDocData();
                // =========== CREATE PARENT DOC ===========
                $docData[self::ATTRIBUTE_FIELD] = ['name' => 'entity'];
                $documents[] = new Document($docId, $docData, $this->getIndex());
            }
            foreach ($entity->getAttributeValues() as $uniqueKey => $attributeValue) {
                $attribute = $this->getAttribute($uniqueKey);
                if ($attribute === null) {
                    continue;
                }
                if (empty($attributeValue)) {
                    continue;
                }
                //=========== CREATE/UPDATE CHILDREN DOCS ===========
                $document = $this->documents[$docId . '_' . $uniqueKey] ?? null;
                if ($document === null) {
                    $docData = $entity->createDocData($attribute);
                    $docData[self::ATTRIBUTE_FIELD] = [
                        'name'   => 'attribute',
                        'parent' => $docId
                    ];
                    $documents[] = new Document($docId . '_' . $uniqueKey, $docData, $this->getIndex());
                } elseif ($document instanceof Document) {
                    $docData = $document->getData();
                    if ($entity->updateDocData($docData, $attribute)) {
                        //dump($docData);
                        if (!empty($docData)) {
                            $document->setData($docData);
                            $documents[] = $document;
                        }
//                        else {
//                            // remove doc
//                        }
                    }
                }
            }
        }
        if (empty($documents)) {
            return;
        }
        foreach (array_chunk($documents, 500) as $docs) {
            $bulk = new Bulk($this->getIndex()->getClient());
            $bulk->setRequestParam('routing', 1);
            $bulk->setRequestParam('refresh', true);
            $bulk->addDocuments($docs);
            try {
                $response = $bulk->send();
                if (!$response->isOk()) {
                    $this->getFlashBag()->add('error', 'An error occurred during saving. ' . $response->getErrorMessage());
                }
            } catch (Exception $e) {
                $this->getFlashBag()->add('error', 'An error occurred during saving');
                $this->getFlashBag()->add('info', $e->getMessage());
            }
        }
        $this->entities->clear();
    }

    protected function getIndex(): Index
    {
        return $this->getIndexManager()->getIndex('parent_child');
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
                            'type'      => 'join',
                            'relations' => [
                                'entity' => 'attribute'
                            ]
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
}