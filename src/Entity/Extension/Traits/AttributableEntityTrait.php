<?php /** @noinspection PhpUnused */

namespace App\Entity\Extension\Traits;


use App\Entity\Category;
use App\Entity\Extension\AttributableEntity;
use App\EventSubscriber\AttributeHandler;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Extension\Annotation as AppORM;


/**
 * Class AttributeEntityTrait
 *
 * since: 16.09.2021
 * author: alexej@kisselev.de
 */
trait AttributableEntityTrait
{
    private array $attributeValues = [];
    private AttributeHandler $aes;
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
     * @param AttributeHandler $aes
     */
    public function setAttributeValueHandler(AttributeHandler $aes): void
    {
        $this->aes = $aes;
    }

    public function getAttributeHandler(): AttributeHandler
    {
        return $this->aes;
    }


    public function __get($name)
    {
        // get value
        $value = $this->getAttributeHandler()->getAttributeValue($name, $this);
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