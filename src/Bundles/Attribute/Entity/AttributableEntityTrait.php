<?php /** @noinspection PhpUnused */

namespace App\Bundles\Attribute\Entity;


use App\Bundles\Attribute\AttributeManagerEntityInterface;
use App\Bundles\Attribute\AttributeManager;
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
    private array $attributeValues = [];
    private AttributeManager $attributeManager;
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
     * @param AttributeManager $manager
     */
    public function setAttributeManager(AttributeManagerEntityInterface $manager): void
    {
        $this->attributeManager = $manager;
    }

    public function getAttributeManager(): AttributeManager
    {
        return $this->attributeManager;
    }


    public function __get($name)
    {
        // get value
        $value = $this->getAttributemanager()->getAttributeValue($name, $this);
        // overloading property
        $this->{$name} = $value;
        // show value
        return $value;
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
        return $this->attributeValues;
    }

    abstract public function getId(): int;
}