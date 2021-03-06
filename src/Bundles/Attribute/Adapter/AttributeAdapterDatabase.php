<?php /** @noinspection DuplicatedCode */

/**
 * Class AttributeManagerDatabase
 * @package App\Bundles\Attribute\Manager
 *
 * since: 01.07.2022
 * author: alexej@kisselev.de
 */

namespace App\Bundles\Attribute\Adapter;

use App\Entity\AttributeValue;
use App\Entity\Extension\Attributable\AttributableEntity;
use App\Repository\AttributeValueRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use Symfony\Component\Security\Core\User\UserInterface;

class AttributeAdapterDatabase extends AbstractAttributeAdapter
{
    public function search(QueryBuilder $qb, SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields): void
    {
        $searchString = $searchDto->getQuery();
        if (empty($searchString)) {
            return;
        }
        /** @var AttributeValueRepository $dao */
        $dao = $this->getEntityManager()->getRepository(AttributeValue::class);
        $result = $dao->search($searchString);

        if (!empty($result)) {
            $expr = $qb->expr()->in('entity.id', array_column($result, 'attributableId'));
            $qb->orWhere($expr);
        }
    }

    public function getAttributeValues(AttributableEntity $entity): array
    {
        if ($this->attributeValues === null) {
            $this->attributeValues = [];
            // update attribute values. can be lazy loading
            $attributeValues = $this->getAttrValues();
            /** @var AttributeValue $attributeValue */
            foreach ($attributeValues as $attributeValue) {
                $docId = $this->getDocId($attributeValue);
                $this->attributeValues[$docId] = $attributeValue;
            }
        }
        if (array_key_exists($this->getDocumentId($entity), $this->attributeValues)) {
            $attributeValue = $this->attributeValues[$this->getDocumentId($entity)];
            $attrValues = [];
            foreach ($attributeValue->getDocData() as $uniqueKey => $value) {
                $attrValues[$uniqueKey] = $this->convertValue($uniqueKey, $value);
            }
            return $attrValues;
        }
        return [];
    }

    protected function getAttrValues(): array
    {
        if ($this->entities->isEmpty()) {
            return [];
        }
        $entities = new ArrayCollection();
        $scopes = [];
        $ids = [];
        /** @var AttributableEntity $entity */
        foreach ($this->entities as $entity) {
            if ($entity->isLoaded()) {
                continue;
            }
            $entities->add($entity);
            $scopes[] = $this->getScope($entity);
            $ids[] = $entity->getId();
        }
        if ($entities->isEmpty()) {
            return [];
        }
        /** @var AttributeValueRepository $dao */
        $dao = $this->getEntityManager()->getRepository(AttributeValue::class);
        return $dao->findBy([
            'scope'          => array_unique($scopes),
            'attributableId' => $ids
        ]);
    }

    private function getDocId(AttributeValue $entity): string
    {
        return $entity->getScope() . '_' . $entity->getAttributableId();
    }

    public function flush(): void
    {
        if ($this->entities->isEmpty()) {
            return;
        }
        if ($this->attributeValues === null) {
            // can be during synchronizing
            $attributeValues = $this->getAttrValues();
            /** @var AttributeValue $attributeValue */
            foreach ($attributeValues as $attributeValue) {
                $docId = $this->getDocId($attributeValue);
                $this->attributeValues[$docId] = $attributeValue;
            }
        }

        $modified = [];
        //upsert entity documents
        $user = $this->getSecurity()->getUser();
        $identifier = $user instanceof UserInterface ? $user->getUserIdentifier() : 'console';
        /** @var AttributableEntity $entity */
        foreach ($this->entities as $entity) {
            $attrValues = [];
            $docId = $this->getDocumentId($entity);

            foreach ($entity->getAttributeValues() as $uniqueKey => $attrValue) {
                $attribute = $this->getAttribute($uniqueKey);
                if ($attribute === null) {
                    continue;
                }
                $attrValues[$uniqueKey] = $attrValue;
            }
            $tags = '';
            if (is_array($attrValues)) {
                $tagsArray = [];
                foreach ($attrValues as $attrVal) {
                    if (!is_array($attrVal)) {
                        $tagsArray[] = $attrVal;
                    } else {
                        $tagsArray[] = implode('|', $attrVal);
                    }
                }
                $tags = implode('|', $tagsArray);
            }

            /** @var AttributeValue $attributeValue */
            $attributeValue = $this->attributeValues[$docId] ?? null;
            if ($attributeValue instanceof AttributeValue) {
                $docDataOld = $attributeValue->getDocData();
                if ($docDataOld === $attrValues) {
                    continue;
                }
                $attributeValue
                    ->setDocData($attrValues)
                    ->setTags($tags);
                $attributeValue->setUpdatedAt(new DateTime());
                $attributeValue->setUpdatedBy($identifier);
            } else {
                $attributeValue = new AttributeValue();
                $attributeValue
                    ->setAttributableId($entity->getId())
                    ->setScope($this->getScope($entity))
                    ->setDocData($attrValues)
                    ->setTags($tags);
                $attributeValue->setCreatedAt(new DateTime());
                $attributeValue->setCreatedBy($identifier);
            }

            $modified[] = $attributeValue;
            $this->setUpdatedData($entity);
            $modified[] = $entity;
        }
        if (empty($modified)) {
            return;
        }
        $em = $this->getEntityManager();
        foreach ($modified as $attributeValue) {
            $em->persist($attributeValue);
        }
        $this->entities->clear();
        $em->flush();
    }
}