<?php
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
use Doctrine\ORM\EntityManagerInterface;
use Elastica\Index;
use Elastica\Util;
use Exception;
use FOS\ElasticaBundle\Index\IndexManager;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Security;

abstract class AbstractAttributeManager implements AttributeManagerInterface
{
    public const ATTRIBUTE_FIELD = 'attributes';
    private IndexManager $indexManager;
    private Security $security;
    private EntityManagerInterface $em;
    protected ArrayCollection $entities;
    private ArrayCollection $attributes;
    private FlashBagInterface $flashBag;
    protected array $initialisedEntities = [];
    protected ?array $attributeValues = null;
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
     * @return string
     */
    protected function getDocumentId(AttributableEntity $entity): string
    {
        return $this->getScope($entity) . '_' . $entity->getId();
    }

//============================== ATTRIBUTE VALUES FORMAT/CONVERT ==============================

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

    abstract protected function getIndex(): Index;
}