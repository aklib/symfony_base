<?php

namespace App\Entity\Product;

use App\Entity\Extension\Attributable\AttributeTabInterface;
use App\Entity\Extension\Attributable\AttributeTabTrait;
use App\Entity\Extension\Traits\BlameableEntityTrait;
use App\Entity\Extension\Traits\TimestampableEntityTrait;
use App\Repository\Product\ProductAttributeTabRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProductAttributeTabRepository::class)
 */
class ProductAttributeTab implements AttributeTabInterface
{
    use AttributeTabTrait, TimestampableEntityTrait, BlameableEntityTrait;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Product\ProductAttribute", mappedBy="tab")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private Collection $attributes;
}
