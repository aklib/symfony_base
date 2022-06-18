<?php

namespace App\EventSubscriber;

use App\Entity\Attribute;
use App\Entity\Extension\Annotation\CustomDoctrineAnnotation;
use App\Entity\Extension\AttributableEntity;
use DateTime;
use DateTimeZone;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\EventArgs;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Elastica\Bulk;
use Elastica\Document;
use Elastica\Exception\ResponseException;
use Elastica\Query;
use Elastica\Query\BoolQuery;
use Exception;
use FlorianWolters\Component\Core\StringUtils;
use FOS\ElasticaBundle\Index\IndexManager;
use InvalidArgumentException;
use Symfony\Component\Security\Core\Security;
use Throwable;

class AttributeHandler implements EventSubscriber
{
    private AnnotationReader $reader;
    private Collection $attributableEntities;
    /*
     * attributeValues[scope][entity_id][attribute_unique_key]
     */
    private array $attributeValues = [];
    private IndexManager $indexManager;
    private Security $security;
    private bool $isLoaded = false;
    private Collection $attributes;

    public function __construct(IndexManager $indexManager, Security $security)
    {
        $this->indexManager = $indexManager;
        $this->security = $security;
        $this->reader = new AnnotationReader();
        $this->attributableEntities = new ArrayCollection();
        $this->attributes = new ArrayCollection();
    }

    //============================ DOCTRINE ============================

    public function getSubscribedEvents(): array
    {
        return [
            Events::loadClassMetadata,
            Events::postLoad,
            Events::preUpdate,
            Events::postFlush,
        ];
    }

    public function preUpdate(LifecycleEventArgs $eventArgs): void
    {

    }

    public function postLoad(LifecycleEventArgs $eventArgs): void
    {
        $entity = $eventArgs->getEntity();

        if ($entity instanceof AttributableEntity) {
            $entity->setAttributeValueHandler($this);
            $this->attributableEntities->add($entity);
        }
    }

    public function postFlush(PostFlushEventArgs $eventArgs): void
    {
        if ($this->attributableEntities->isEmpty()) {
            return;
        }

        $documents = [];
        foreach ($this->attributableEntities as $entity) {
            foreach ($entity->getAttributeValues() as $uniqueKey => $attributeValue) {
                $attribute = $this->getAttribute($uniqueKey);
                if ($attribute === null) {
                    continue;
                }
                $document = $this->getDocument($this->getScope($entity), $entity->getId(), $uniqueKey);
                if ($document instanceof Document) {
                    $docData = $rawData = $document->getData();
                    $type = $attribute->getAttributeDefinition()->getType();

                    if (array_key_exists($type, $docData) && $docData[$type] === $attributeValue) {
                        continue;
                    }
                    $docData['type'] = $attribute->getAttributeDefinition()->getType();
                    $docData['uniqueKey'] = $attribute->getUniqueKey();
                    $docData[$attribute->getAttributeDefinition()->getType()] = $attributeValue;
                    $document->setData($docData);

                    try {
                        if (json_encode($rawData, JSON_THROW_ON_ERROR) !== json_encode($docData, JSON_THROW_ON_ERROR)) {
                            $this->setUpdateData($entity, $eventArgs->getEntityManager());
                        }
                    } catch (Exception $e) {
                    }
                } else {
                    $docData = [
                        'id'                                            => $entity->getId(),
                        'scope'                                         => $this->getScope($entity),
                        'type'                                          => $attribute->getAttributeDefinition()->getType(),
                        'uniqueKey'                                     => $attribute->getUniqueKey(),
                        'multiple'                                      => $attribute->isMultiple(),
                        'attribute'                                     => [
                            'id'   => $attribute->getId(),
                            'name' => $attribute->getName()
                        ],
                        $attribute->getAttributeDefinition()->getType() => $attributeValue
                    ];
//                    dump($docData);
                    $document = new Document('', $docData, $this->indexManager->getDefaultIndex());
                    $this->setUpdateData($entity, $eventArgs->getEntityManager());
                }
                $documents[] = $document;
            }
        }
        if (empty($documents)) {
            return;
        }
//        die('post flush');
        $bulk = new Bulk($this->indexManager->getIndex()->getClient());
        foreach ($documents as $document) {
            $bulk->addDocument($document);
        }
        $bulk->send();
        $this->attributableEntities->clear();
        $eventArgs->getEntityManager()->flush();
        sleep(1);
        // save attribute values
    }

    /**
     * @param $uniqueKey
     * @param AttributableEntity $entity
     * @noinspection PhpDocSignatureIsNotCompleteInspection
     */
    public function getAttributeValue($uniqueKey, AttributableEntity $entity)
    {
        if (!$this->isLoaded) {
            $documents = $this->getDocuments($this->attributableEntities);
            /** @var Document $document */
            foreach ($documents as $document) {
                $docData = $document->getData();
                try {
                    $key = $this->getKey($docData['uniqueKey'], null, $docData);
                } catch (Exception $e) {
                    error_log($e);
                    continue;
                }
                // by unique attributable class (scope) entity id and attribute unique key
                $attribute = $this->getAttribute($docData['uniqueKey']);
                if ($attribute !== null && $attribute->isMultiple()) {
                    $this->attributeValues[$key] = (array)$docData[$docData['type']];
                } else {
                    $this->attributeValues[$key] = $docData[$docData['type']];
                }
            }
            $this->isLoaded = true;
        }
        try {
            $key = $this->getKey($uniqueKey, $entity);
        } catch (Exception $e) {
            return null;
        }
        $attribute = $this->getAttribute($uniqueKey);
        if (!array_key_exists($key, $this->attributeValues)) {
            return null;
        }
        if ($attribute instanceof Attribute) {
            $this->attributeValues[$key] = $this->formatAttributeValue($attribute, $this->attributeValues[$key]);
        }
        return $this->attributeValues[$key] ?? null;
    }

    /**
     * @param Attribute $attribute
     * @param $value
     * @return DateTime|null
     * @noinspection ReturnTypeCanBeDeclaredInspection
     * @noinspection PhpMissingReturnTypeInspection
     */
    protected function formatAttributeValue(Attribute $attribute, $value)
    {
        $formattedValue = $value;
        switch ($attribute->getAttributeDefinition()->getType()) {
            case 'date':
            case 'datetime':
                try {
                    if (is_array($value)) {
                        $formattedValue = new DateTime($value['date'], new DateTimeZone($value['timezone']));
                    }
                } catch (Exception $e) {
                    $formattedValue = null;
                }
                break;
        }
        return $formattedValue;
    }

    /**
     * Unique key for attribute value e.eg. product_20_article_number
     * @param $uniqueKey
     * @param AttributableEntity|null $entity
     * @param array|null $docData
     * @return string
     * @throws Exception
     */
    private function getKey($uniqueKey, AttributableEntity $entity = null, array $docData = null): string
    {
        if ($entity instanceof AttributableEntity) {
            return $this->getScope($entity) . '|' . $entity->getId() . '|' . $uniqueKey;
        }
        if (empty($docData)) {
            throw new InvalidArgumentException('No document data to create a key');
        }
        return $docData['scope'] . '|' . $docData['id'] . '|' . $uniqueKey;
    }

    private function setUpdateData(AttributableEntity $entity, EntityManagerInterface $entityManager): void
    {
        if (method_exists($entity, 'setUpdatedAt')) {
            $entity->setUpdatedAt(new DateTime('now'));
        }

        if (method_exists($entity, 'setUpdatedBy')) {
            $entity->setUpdatedBy($this->security->getUser());
        }
        $entityManager->persist($entity);
    }

    //============================ CUSTOM ANNOTATIONS ============================

    /** @noinspection PhpPossiblePolymorphicInvocationInspection */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $classMetadata = $eventArgs->getClassMetadata();
        foreach ($classMetadata->getReflectionClass()->getProperties() as $property) {
            foreach ($this->reader->getPropertyAnnotations($property) as $annotation) {
                if ($annotation instanceof CustomDoctrineAnnotation) {
                    $key = strtolower(substr(strrchr(get_class($annotation), "\\"), 1));
                    if ($classMetadata->hasAssociation($property->getName())) {
                        $classMetadata->associationMappings[$property->getName()][$key] = (array)$annotation;
                    } else {
                        $classMetadata->fieldMappings[$property->getName()][$key] = (array)$annotation;
                    }
                }
            }
        }
    }

    //============================ ELASTICSEARCH ============================

    public function getAttribute(string $uniqueKey): ?Attribute
    {
        if ($this->attributes->isEmpty()) {
            /** @var AttributableEntity $entity */
            foreach ($this->attributableEntities as $entity) {
                if ($entity->getCategory() === null) {
                    continue;
                }
                foreach ($entity->getCategory()->getAttributes(true) as $attribute) {
                    if (!$this->attributes->contains($attribute)) {
                        $this->attributes->add($attribute);
                    }
                }
            }
        }
        $result = $this->attributes->filter(static function (Attribute $attribute) use ($uniqueKey) {
            // filter by unique key
            if ($attribute->getUniqueKey() === $uniqueKey) {
                return $attribute;
            }
            return null;
        });
        if ($result->isEmpty()) {
            return null;
        }
        return $result->first();
    }


    /**
     * @param string $scope
     * @param int $id
     * @param string $uniqueKey
     * @return Document|null
     */
    public function getDocument(string $scope, int $id, string $uniqueKey): ?Document
    {
        $result = $this->getDocuments()->filter(static function (Document $doc) use ($scope, $id, $uniqueKey) {
            // filter by unique key
            $docData = $doc->getData();
            if ($docData['scope'] === $scope && $docData['id'] === $id && $docData['uniqueKey'] === $uniqueKey) {
                return $doc;
            }
            return null;
        });
        if ($result->isEmpty()) {
            return null;
        }
        return $result->first();
    }

    public function getDocuments(Collection $entities = null): Collection
    {
        $documents = new ArrayCollection();
        if ($entities === null) {
            $entities = $this->attributableEntities;
        }
        if ($entities->isEmpty()) {
            return $documents;
        }
        // load documents from elasticsearch
        $query = $this->getQuery($entities);
        $query->setSize(9999);
        try {
            $results = $this->indexManager->getIndex()->search($query)->getDocuments();
            foreach ($results as $document) {
                $documents->add($document);
            }
        } catch (Exception $e) {
            error_log($e);
            if ($e instanceof ResponseException) {
                // highly likely the index doesn't exist
                // create one
                $this->createIndexIfNotExists();
                // repeat the method call
                return $this->getDocuments($entities);
            }
        }
        return $documents;
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

    /**
     * Creates an elasticsearch index if it not exists
     * @return bool
     */
    public function createIndexIfNotExists(): bool
    {
        if (!$this->indexManager->getIndex()->exists()) {
            $this->indexManager->getIndex()->create([
                'settings' => [
                    'number_of_shards'   => 1,
                    'number_of_replicas' => 0
                ],
                'mappings' => [
                    'properties' => [
                        'attribute' => ['type' => 'nested']
                    ]
                ],
            ]);
            return true;
        }
        return false;
    }

    protected function getScope(AttributableEntity $entity): string
    {
        return strtolower(StringUtils::substringAfterLast(get_class($entity), "\\"));
    }
}
