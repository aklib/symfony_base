<?php /** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection PhpUnused */

/**
 * Class ElasticaManager
 * @package App\Bundles\Attribute
 *
 * since: 23.06.2022
 * author: alexej@kisselev.de
 */

namespace App\Bundles\Attribute;

use App\Bundles\Attribute\Entity\AttributableEntity;
use App\Entity\Attribute;
use App\Entity\User;
use DateTime;
use DateTimeZone;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Elastica\Bulk;
use Elastica\Document;
use Elastica\Exception\ResponseException;
use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Util;
use Exception;
use FlorianWolters\Component\Core\StringUtils;
use FOS\ElasticaBundle\Index\IndexManager;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Security;
use Throwable;

class AttributeManager implements AttributeManagerEntityInterface
{
    public const ATTRIBUTE_FIELD = 'attributes';
    private IndexManager $indexManager;
    private Security $security;
    private Collection $entities;
    private ArrayCollection $documents;
    private ArrayCollection $attributes;
    private FlashBagInterface $flashBag;
    private array $initialisedEntities = [];
    private array $attributeValues = [];
    private array $user = [];

    public function __construct(IndexManager $indexManager, Security $security, FlashBagInterface $flashBag)
    {
        $this->indexManager = $indexManager;
        $this->security = $security;
        $this->flashBag = $flashBag;
        $this->entities = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->attributes = new ArrayCollection();
    }

    // =============================== WORKFLOW METHODS  ===============================


    public function getAttributeValue(string $uniqueKey, AttributableEntity $entity)
    {
        $key = $this->getKey($uniqueKey, $entity);
        if (array_key_exists($key, $this->attributeValues)) {
            return $this->attributeValues[$this->getKey($uniqueKey, $entity)];
        }
        // update attribute values. can be lazy loading
        $documents = $this->getDocuments();
        /** @var Document $document */
        foreach ($documents as $document) {
            $docData = $document->getData();
            $attributes = $docData[self::ATTRIBUTE_FIELD] ?? [];
            foreach ($attributes as $attrData) {
                $valueKey = $this->getKey($attrData['uniqueKey'], null, $docData);
                $this->attributeValues[$valueKey] = $this->convertValue($attrData['uniqueKey'], $attrData[$attrData['type']] ?? null);
            }
        }
        /** @var Attribute $attribute */
        foreach ($entity->getCategory()->getAttributes(true) as $attribute) {
            $valueKey = $this->getKey($attribute->getUniqueKey(), $entity);
            if (!array_key_exists($valueKey, $this->attributeValues)) {
                $this->attributeValues[$valueKey] = null;
            }
        }
        return $this->attributeValues[$key] ?? null;
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
            $key = $this->getKey('', $entity);
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
            $results = $this->indexManager->getIndex()->search($query)->getDocuments();
            foreach ($results as $document) {
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
                $document->setData($docData);
            } else {
                $docData = [
                    'id'                  => $entity->getId(),
                    'scope'               => $this->getScope($entity),
                    self::ATTRIBUTE_FIELD => $attributeValues
                ];
                $document = new Document('', $docData, $this->indexManager->getDefaultIndex());
            }
            $documents[] = $document;
        }

        if (empty($documents)) {
            return;
        }

        $bulk = new Bulk($this->indexManager->getIndex()->getClient());
//        $bulk->setRequestParam('routing', 1);
        $bulk->setRequestParam('refresh', true);
        $bulk->addDocuments($documents);

        try {

            $response = $bulk->send();
            if ($response->isOk()) {
                $this->getFlashBag()->add('success', 'Attributes have been saved');
            }
        } catch (Exception $e) {
            $this->getFlashBag()->add('error', 'An error occurred during saving');
            $this->getFlashBag()->add('info', $e->getMessage());
        }

        $this->entities->clear();
//        $eventArgs->getEntityManager()->flush();
//        sleep(1);
        // save attribute values
    }

    public function getAttribute(string $uniqueKey): ?Attribute
    {
        $result = $this->attributes->filter(static function (Attribute $attribute) use ($uniqueKey) {
            // filter by unique key
            if ($attribute->getUniqueKey() === $uniqueKey) {
                return $attribute;
            }
            return null;
        });
//        if ($result->isEmpty()) {
//            $attr = $this->getEntityManager()->getRepository(Attribute::class)->findOneByUniqueKey($uniqueKey);
//            if ($attr instanceof Attribute) {
//                $this->attributes->add($attr);
//            }
//            return $attr;
//        }
        return $result->first();
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
            if (!array_key_exists(self::ATTRIBUTE_FIELD, $docData) || !array_key_exists('uniqueKey', $docData[self::ATTRIBUTE_FIELD])) {
                // attributable entity
                return null;
            }
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
     * Unique key for attribute value e.eg. product_20_article_number
     * @param $uniqueKey
     * @param AttributableEntity|null $entity
     * @param array|null $docData
     * @return string
     */
    protected function getKey($uniqueKey, AttributableEntity $entity = null, array $docData = null): string
    {
        if ($entity instanceof AttributableEntity) {
            return $this->getScope($entity) . '|' . $entity->getId() . '|' . $uniqueKey;
        }
        return $docData['scope'] . '|' . $docData['id'] . '|' . $uniqueKey;
    }


    protected function convertDateTime(DateTime $dateTime = null, bool $includeTimezone = true): string
    {
        if ($dateTime === null) {
            try {
                $dateTime = new DateTime('now');
            } catch (Exception $e) {
            }
        }
        return Util::convertDateTimeObject($dateTime, $includeTimezone);
    }

    // =============================== INITIALISATION METHODS  ===============================

    public function addEntity(AttributableEntity $entity): void
    {
        if (!$this->entities->contains($entity)) {
            $entity->setAttributeManager($this);
            $this->entities->add($entity);
        }
    }

    public function addAttribute(Attribute $attribute): void
    {
        if (!$this->attributes->contains($attribute)) {
            $this->attributes->add($attribute);
        }
    }

    /**
     * Creates an elasticsearch index if it not exists
     * @return bool
     */
    private function createIndexIfNotExists(): bool
    {
        if (!$this->indexManager->getIndex()->exists()) {
            $this->indexManager->getIndex()->create([
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

    // =============================== HELP METHODS  ===============================


    /**
     * @return Security
     */
    protected function getSecurity(): Security
    {
        return $this->security;
    }

    /**
     * @return FlashBagInterface
     */
    private function getFlashBag(): FlashBagInterface
    {
        return $this->flashBag;
    }

    /**
     * @return ArrayCollection
     */
    protected function getAttributes(): ArrayCollection
    {
        return $this->attributes;
    }

    /**
     * @return ArrayCollection|Collection
     */
    protected function getEntities()
    {
        return $this->entities;
    }

    /**
     * @return IndexManager
     */
    protected function getIndexManager(): IndexManager
    {
        return $this->indexManager;
    }

    protected function getScope(AttributableEntity $entity): string
    {
        return strtolower(StringUtils::substringAfterLast(get_class($entity), "\\"));
    }

    /**
     * @param string $uniqueKey
     * @param $attributeValue
     * @param $download true - show in the user interface, false = upload into elasticsearch
     * @return array|DateTime|mixed|null
     */
    protected function convertValue(string $uniqueKey, $attributeValue, bool $download = true)
    {
        $formattedValue = $attributeValue;
        $attribute = $this->getAttribute($uniqueKey);
        if ($attribute === null) {
            return $attributeValue;
        }
        $type = $attribute->getAttributeDefinition()->getType();
        switch ($type) {
            case 'date':
            case 'datetime':
                if ($download) {
                    try {
                        if (is_array($attributeValue)) {
                            $formattedValue = new DateTime($attributeValue['date'], new DateTimeZone($attributeValue['timezone']));
                        } elseif (is_string($attributeValue)) {
                            $formattedValue = new DateTime($attributeValue);
                        }
                    } catch (Exception $e) {
                        $formattedValue = null;
                    }
                } elseif ($attributeValue instanceof DateTime) {
                    $formattedValue = $this->convertDateTime($attributeValue, $type === 'datetime');
                }
                break;
            default:
                if (!$attribute->isMultiple()) {
                    if (is_array($attributeValue)) {
                        $formattedValue = array_shift($attributeValue);
                    }
                } else {
                    $formattedValue = (array)$attributeValue;
                }
        }
        return $formattedValue;
    }

    /**
     * @param AttributableEntity $entity
     * @param EntityManagerInterface $entityManager
     * @return void
     * @noinspection PhpUnusedPrivateMethodInspection
     */
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

    /**
     * @return array
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function getUserData(): array
    {
        if (empty($this->user)) {
            $user = $this->security->getUser();
            if ($user instanceof User) {
                $this->user = [
                    'id'   => $user->getId(),
                    'name' => (string)$user,
                ];
            }
        }
        return $this->user;
    }
}