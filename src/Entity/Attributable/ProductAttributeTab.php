<?php

namespace App\Entity\Attributable;

use App\Entity\Attributable\Extension\AttributeTabInterface;
use App\Entity\Attributable\Extension\AttributeTabTrait;
use App\Entity\Extension\Traits\BlameableEntityTrait;
use App\Entity\Extension\Traits\TimestampableEntityTrait;
use App\Repository\Attributable\ProductAttributeTabRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProductAttributeTabRepository::class)
 */
class ProductAttributeTab implements AttributeTabInterface
{
    use AttributeTabTrait, TimestampableEntityTrait, BlameableEntityTrait;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Attributable\ProductAttribute", mappedBy="tab")
     * @ORM\OrderBy({"name" = "ASC"})
     */
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

}
