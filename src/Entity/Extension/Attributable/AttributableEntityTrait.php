<?php /** @noinspection PhpUnused */

namespace App\Entity\Extension\Attributable;


use App\Bundles\Attribute\Adapter\Interfaces\AttributeValueAdapterInterface;
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
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="App\Entity\Generator\SequenceGenerator")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $name = '';

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $active = true;
    private ?CategoryInterface $category = null;
    private AttributeValueAdapterInterface $attributeManager;
    private ?string $scope = null;
    private ?array $attributeValues = null;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return AttributableEntity
     */
    public function setName(string $name): AttributableEntity
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): AttributableEntity
    {
        $this->active = $active;
        return $this;
    }

    public function getCategory(): ?CategoryInterface
    {
        return $this->category;
    }

    public function setCategory(CategoryInterface $category): self
    {
        $this->category = $category;

        return $this;
    }

    //==================================== HANDLE ATTRIBUTE VALUES ====================================

    public function setAttributeManager(AttributeValueAdapterInterface $manager): AttributableEntity
    {
        $this->attributeManager = $manager;
        return $this;
    }

    public function getAttributeManager(): AttributeValueAdapterInterface
    {
        return $this->attributeManager;
    }

    public function setAttributeValues(array $attributeValues = null): AttributableEntity
    {
        if ($this->attributeValues === null) {
            if ($attributeValues === null) {
                $this->attributeValues = $this->getAttributemanager()->getAttributeValues($this);
            } else {
                $this->attributeValues = $attributeValues;
            }
            foreach ($this->attributeValues as $uniqueKey => $attributeValue) {
                $this->{$uniqueKey} = $attributeValue;
            }
        }
        return $this;
    }

    private function getAttributeValue(string $name)
    {
        $this->setAttributeValues();
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

    public function __toString()
    {
        return $this->getName() ?? '';
    }

    /**
     * @return array
     */
    public function getAttributeValues(): array
    {
        $this->setAttributeValues();
        return $this->attributeValues ?? [];
    }

    public function getScope(): string
    {
        if ($this->scope === null) {
            $this->scope = Util::toSnakeCase(substr(strrchr(get_class($this), '\\'), 1));
        }
        return $this->scope;
    }

    public function createDocData(AttributeInterface $attribute = null): ?array
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
        $type = $attribute->getAttributeDef()->getType();
        $docData['uniqueKey'] = $uniqueKey;
        $docData['attribute']['id'] = $attribute->getId();
        $docData['type'] = $type;
        $docData[$type] = $this->getAttributeManager()->convertValue($uniqueKey, $this->attributeValues[$uniqueKey], false);
        return $docData;
    }

    public function updateDocData(array &$docData, AttributeInterface $attribute = null): bool
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

    public function isDeletable(): bool
    {
        return true;
    }
}