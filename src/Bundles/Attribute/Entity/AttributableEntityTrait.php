<?php /** @noinspection PhpUnused */

namespace App\Bundles\Attribute\Entity;


use App\Bundles\Attribute\AttributeValueManagerInterface;
use App\Entity\Category;
use App\Entity\Extension\Annotation as AppORM;
use Doctrine\ORM\Mapping as ORM;


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

    abstract public function getId(): int;
}