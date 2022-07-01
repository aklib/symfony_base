<?php /** @noinspection PhpUnused */
/** @noinspection PhpUnusedPrivateMethodInspection */

/**
 * Class AbstractAttributeManager
 * @package App\Bundles\Attribute\Manager
 *
 * since: 29.06.2022
 * author: alexej@kisselev.de
 */

namespace App\Bundles\Attribute\Manager;

use App\Bundles\Attribute\AttributeManagerInterface;
use App\Bundles\Attribute\Entity\AttributableEntity;
use App\Entity\Attribute;
use App\Entity\User;
use DateTime;
use DateTimeZone;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use Elastica\Aggregation\Terms;
use Elastica\Index;
use Elastica\Query;
use Elastica\Util;
use Exception;
use FOS\ElasticaBundle\Index\IndexManager;
use Laminas\Json\Json;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Security;

abstract class AbstractAttributeManager implements AttributeManagerInterface
{
    public const ATTRIBUTE_FIELD = 'attributes';
    private IndexManager $indexManager;
    private Security $security;
    private EntityManagerInterface $em;
    private ArrayCollection $attributes;
    private FlashBagInterface $flashBag;
    protected array $initialisedEntities = [];
    protected ?array $attributeValues = null;
    protected ArrayCollection $entities;
    private array $user = [];

    public function __construct(IndexManager $indexManager, Security $security, FlashBagInterface $flashBag, EntityManagerInterface $em)
    {
        $this->indexManager = $indexManager;
        $this->security = $security;
        $this->em = $em;
        $this->flashBag = $flashBag;
        $this->entities = new ArrayCollection();
        $this->attributes = new ArrayCollection();
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
        if ($result->isEmpty()) {
            $attr = $this->getEntityManager()->getRepository(Attribute::class)->findOneByUniqueKey($uniqueKey);
            if ($attr instanceof Attribute) {
                $this->attributes->add($attr);
            }
            return $attr;
        }
        return $result->first();
    }

    // =============================== INITIALISATION METHODS  ===============================

    public function addEntity(AttributableEntity $entity): void
    {
        if (!$this->entities->contains($entity)) {
            $entity->setAttributeManager($this);
            $this->entities->add($entity);
        }
    }

    public function removeEntity(AttributableEntity $entity): void
    {
        if ($this->entities->contains($entity)) {
            $this->entities->removeElement($entity);
        }
    }

    public function addAttribute(Attribute $attribute): void
    {
        if (!$this->attributes->contains($attribute)) {
            $this->attributes->add($attribute);
        }
    }

    // =============================== HELP METHODS  ===============================
    protected function getScope(AttributableEntity $entity): string
    {
        return Util::toSnakeCase(substr(strrchr(get_class($entity), '\\'), 1));
    }

    /**
     * Unique key for document e.g. product_20,  user_profile_1
     * @param AttributableEntity|null $entity
     * @param array|null $docData
     * @return string
     */
    protected function getDocumentId(AttributableEntity $entity = null, array $docData = null): string
    {
        if ($entity instanceof AttributableEntity) {
            return $this->getScope($entity) . '_' . $entity->getId();
        }
        if (is_array($docData) && array_key_exists('scope', $docData)) {
            return $docData['scope'] . '_' . $docData['id'];
        }
        if (is_array($docData) && array_key_exists(self::ATTRIBUTE_FIELD, $docData) && array_key_exists('parent', $docData[self::ATTRIBUTE_FIELD])) {
            return $docData[self::ATTRIBUTE_FIELD]['parent'];
        }
        return '';
    }

//============================== ATTRIBUTE VALUES FORMAT/CONVERT ==============================

    /**
     * Loads elastica documents.
     * Documents can be loaded subsequently for lazy loaded entities
     * @param bool $asDocument
     * @return array
     */
    protected function getDocuments(bool $asDocument = false): array
    {
        if ($this->entities->isEmpty()) {
            return [];
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
            return [];
        }
        $documents = [];
        // load documents from elasticsearch
        $query = $this->getQuery($entities);
        $query->setSize(9999);
        try {
            if ($asDocument) {
                $docs = $this->getIndex()->search($query)->getDocuments();
                foreach ($docs as $doc) {
                    $documents[$doc->getId()] = $doc;
                }
            } else {
                $response = $this->getIndex()->search($query)->getResponse();
                $hits = $response->getData()['hits']['hits'] ?? [];
                foreach ($hits as $document) {
                    $documents[$document['_id']] = $document['_source'];
                }
            }

        } catch (Exception $e) {
            error_log($e);
            $created = $this->createIndexIfNotExists();
            if (!$created) {
                $this->getFlashBag()->add('warning', 'An error occurred during getting. ' . $e->getMessage());
            }
        }
        return $documents;
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

    /**
     * @param string $uniqueKey
     * @param $attributeValue
     * @param $download true - show in the user interface, false = upload into elasticsearch
     * @return array|DateTime|mixed|null
     */
    public function convertValue(string $uniqueKey, $attributeValue, bool $download = true)
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
            case 'birthday':
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
            case 'address':
                // don't touch!
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

//============================== CRUD CONTROLLER ==============================

    public function search(QueryBuilder $qb, SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields): void
    {
        if (!is_a($entityDto->getFqcn(), AttributableEntity::class, true)) {
            return;
        }
        $searchQuery = $searchDto->getQuery();
        if (empty($searchQuery)) {
            return;
        }
        if (is_numeric($searchQuery)) {
            //todo: need to be improved
            return;
        }

        /** @var Query $query */
        $query = $this->getSearchQuery($searchDto, $entityDto, $fields);
        if ($query === null) {
            return;
        }

        $query->setSize(0);
        $aggTerm = new Terms('count');
        $aggTerm->setField('id');
        $aggTerm->setSize(9999);
        $query->addAggregation($aggTerm);
        $resultSet = $this->getIndex()->search($query);
        $total = $resultSet->getTotalHits();
        if ($total > 200) {
            $this->getFlashBag()->add('warning', "The search delivers too many[$total] hits, you may have to repeat searching with a larger number of initials.");
            return;
        }
        $aggregation = $resultSet->getAggregation('count');
        $ids = array_column($aggregation['buckets'], 'key');
        if ($total > 0) {
            $expr = $qb->expr()->in('entity.id', $ids);
            $qb->andWhere($expr);
        }

//        $this->printQuery($query);die;
    }

//============================== GETTERS ==============================

    /**
     * @return Security
     * @noinspection PhpUnused
     */
    protected function getSecurity(): Security
    {
        return $this->security;
    }

    /**
     * @return FlashBagInterface
     */
    protected function getFlashBag(): FlashBagInterface
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
     * @return IndexManager
     */
    protected function getIndexManager(): IndexManager
    {
        return $this->indexManager;
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->em;
    }

    /**
     * @param Query $query
     */
    protected function printQuery(Query $query): void
    {
        $out = '<pre>';
        $out .= 'GET ' . $this->getIndex()->getName() . "/_search\n";
        $json = Json::encode($query->toArray());
        $out .= Json::prettyPrint($json, ['indent' => '  ']);
        $out .= '</pre><br>';
        echo $out;
    }

    abstract protected function getIndex(): Index;

    abstract protected function getQuery(Collection $entities): Query;

    abstract protected function getSearchQuery(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields): ?Query;

    abstract protected function createIndexIfNotExists(): bool;
}