<?php /** @noinspection PhpUnusedPrivateMethodInspection */

/**
 * Class AbstractAttributeManager
 * @package App\Bundles\Attribute\Manager
 *
 * since: 01.07.2022
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
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use Elastica\Util;
use Exception;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Security;

abstract class AbstractAttributeManager implements AttributeManagerInterface
{
    private Security $security;
    private EntityManagerInterface $em;
    private ArrayCollection $attributes;
    private SessionInterface $session;
    protected array $initialisedEntities = [];
    protected ?array $attributeValues = null;
    /**
     * @var ArrayCollection<AttributableEntity>
     */
    protected ArrayCollection $entities;
    private array $user = [];

    public function __construct(Security $security, SessionInterface $session, EntityManagerInterface $em)
    {
        $this->security = $security;
        $this->em = $em;
        $this->session = $session;
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
        return $this->getScopeFromFqcn(get_class($entity));
    }

    public function getScopeFromFqcn(string $fqcn): string
    {
        return Util::toSnakeCase(substr(strrchr($fqcn, '\\'), 1));
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
        return '';
    }

    protected function setUpdatedData(AttributableEntity $entity): void
    {
        if (method_exists($entity, 'setUpdatedAt')) {
            $entity->setUpdatedAt(new DateTime());
        }

        if (method_exists($entity, 'setUpdatedBy')) {
            $entity->setUpdatedBy($this->getSecurity()->getUser());
        }
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
        return $this->session->getFlashBag();
    }

    /**
     * @return ArrayCollection
     */
    protected function getAttributes(): ArrayCollection
    {
        return $this->attributes;
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->em;
    }


    abstract public function search(QueryBuilder $qb, SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields): void;
}