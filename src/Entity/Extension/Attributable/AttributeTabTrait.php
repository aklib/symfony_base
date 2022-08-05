<?php /** @noinspection PhpUnused */

/**
 * Class AttributeTabTrait
 * @package App\Entity\Attributable\Extension
 *
 * since: 04.07.2022
 * author: alexej@kisselev.de
 */

namespace App\Entity\Extension\Attributable;

use App\Entity\Product\ProductAttributeTab;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

trait AttributeTabTrait
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="App\Entity\Generator\SequenceGenerator")
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $id;

    /**
     * @var string
     *
     * @ORM\Column(name="label", type="string", length=64, nullable=false)
     */
    private string $name;

    /**
     * @var int
     *
     * @ORM\Column(name="sort_order", type="integer", nullable=false, options={"default"="1"})
     */
    private int $sortOrder = 1;

    private Collection $attributes;

    public function __construct()
    {
        $this->attributes = new ArrayCollection();
    }

    /**
     * @return Collection
     */
    public function getAttributes(): Collection
    {
        return $this->attributes;
    }

    /**
     * @param ArrayCollection|Collection $attributes
     * @return ProductAttributeTab
     */
    public function setAttributes($attributes): ProductAttributeTab
    {
        $this->attributes = $attributes;
        return $this;
    }

    public function isDeletable(): bool
    {
        return $this->getAttributes()->count() === 0;
    }

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
     * @return AttributeTabInterface
     */
    public function setName(string $name): AttributeTabInterface
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return int
     */
    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    /**
     * @param int $sortOrder
     * @return AttributeTabInterface
     */
    public function setSortOrder(int $sortOrder): AttributeTabInterface
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }
}