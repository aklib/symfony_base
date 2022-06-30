<?php /** @noinspection PhpUnused */

namespace App\Bundles\Attribute\Entity;


use App\Bundles\Attribute\AttributeValueManagerInterface;
use App\Entity\Attribute;
use App\Entity\Category;
use App\Entity\Extension\Annotation as AppORM;
use Doctrine\ORM\Mapping as ORM;
use Elastica\Util;


/**
 * Class AttributeEntityTrait
 *
 * since: 16.09.2021
 * author: alexej@kisselev.de
 */
trait AttributableEntityTrait
{
    private ?array $attributeValues = null;
    private AttributeValueManagerInterface $attributeManager;
    /**
     * @ORM\ManyToOne(targetEntity=Category::class, fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     * @AppORM\Element(sortOrder="3")
     *
     */
    private ?Category $category = null;
    private ?string $scope = null;

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    //==================================== HANDLE ATTRIBUTE VALUES ====================================

    /**
     * @implements AttributableEntity
     * @param AttributeValueManagerInterface $manager
     */
    public function setAttributeManager(AttributeValueManagerInterface $manager): void
    {
        $this->attributeManager = $manager;
    }

    public function getAttributeManager(): AttributeValueManagerInterface
    {
        return $this->attributeManager;
    }

    private function getAttributeValue(string $name)
    {
        if ($this->attributeValues === null) {
            $this->attributeValues = $this->getAttributemanager()->getAttributeValues($this);
            foreach ($this->attributeValues as $uniqueKey => $attributeValue) {
                $this->{$uniqueKey} = $attributeValue;
            }
        }
        return $this->attributeValues[$name] ?? null;
    }

    public function __get($name)
    {
        return $this->getAttributeValue($name);
    }

    public function __set($name, $value)
    {
        $this->attributeValues[$name] = $value;
    }

    public function __isset($name)
    {
        return isset($this->attributeValues[$name]);
    }

    /**
     * @return array
     */
    public function getAttributeValues(): array
    {
        return $this->attributeValues ?? [];
    }

    public function getScope(): string
    {
        if ($this->scope === null) {
            $this->scope = Util::toSnakeCase(substr(strrchr(get_class($this), '\\'), 1));
        }
        return $this->scope;
    }

    public function createDocData(Attribute $attribute = null): ?array
    {
        // create entity doc

        $docData = [
            'id'    => $this->getId(),
            'scope' => $this->getScope()
        ];

        if ($attribute === null) {
            return $docData;
        }
        $uniqueKey = $attribute->getUniqueKey();
        if (!array_key_exists($uniqueKey, $this->attributeValues) || $this->attributeValues[$uniqueKey] === null) {
            return null;
        }
        // create attribute value doc
        $type = $attribute->getAttributeDefinition()->getType();
        $docData['uniqueKey'] = $uniqueKey;
        $docData['attribute']['id'] = $attribute->getId();
        $docData['type'] = $type;
        $docData[$type] = $this->getAttributeManager()->convertValue($uniqueKey, $this->attributeValues[$uniqueKey], false);
        return $docData;
    }

    public function updateDocData(array &$docData, Attribute $attribute = null): bool
    {
        $newDocData = $this->createDocData($attribute);
        if ($newDocData === null) {
            $docData = [];
            return true;
        }
        $docData2 = array_replace_recursive($docData, $newDocData);

        $changed = false;
        if ($docData2 !== $docData) {
            $docData = $docData2;
            $changed = true;
        }
        return $changed;
    }

    abstract public function getId(): int;
}