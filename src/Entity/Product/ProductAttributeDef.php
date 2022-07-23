<?php

namespace App\Entity\Product;

use App\Entity\Extension\Attributable\AttributeDefInterface;
use App\Entity\Extension\Attributable\AttributeDefTrait;
use App\Entity\Extension\Traits\BlameableEntityTrait;
use App\Entity\Extension\Traits\TimestampableEntityTrait;
use App\Repository\Product\ProductAttributeDefRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="attribute_definition", uniqueConstraints={@ORM\UniqueConstraint(name="UNIQ_6C5628BD8CDE5729", columns={"type"})})
 * @ORM\Entity(repositoryClass=ProductAttributeDefRepository::class)
 */
class ProductAttributeDef implements AttributeDefInterface
{
    use AttributeDefTrait, TimestampableEntityTrait, BlameableEntityTrait;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Product\ProductAttribute", mappedBy="attributeDef")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private Collection $attributes;
}
