<?php

namespace App\Entity\Product;


use App\Entity\Extension\Annotation as AppORM;
use App\Entity\Extension\Attributable\AttributeDefInterface;
use App\Entity\Extension\Attributable\AttributeInterface;
use App\Entity\Extension\Attributable\AttributeTabInterface;
use App\Entity\Extension\Attributable\AttributeTrait;
use App\Entity\Extension\Attributable\CategoryInterface;
use App\Entity\Extension\Traits\BlameableEntityTrait;
use App\Entity\Extension\Traits\TimestampableEntityTrait;
use App\Repository\Product\ProductAttributeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=ProductAttributeRepository::class)
 * @UniqueEntity(
 *     fields={"uniqueKey"},
 *     message="The name '{{ value }}' is already in use. Please choose any other one."
 * )
 */
class ProductAttribute implements AttributeInterface
{

    use AttributeTrait, TimestampableEntityTrait, BlameableEntityTrait;

    /**
     * @var CategoryInterface
     * @ORM\ManyToOne(targetEntity=ProductCategory::class, inversedBy="attributes")
     *
     * @ORM\OrderBy({"lft" = "ASC"})
     * @AppORM\Element(sortOrder="3")
     */
    private CategoryInterface $category;

    /**
     * @var AttributeTabInterface
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Product\ProductAttributeTab", fetch="EXTRA_LAZY", inversedBy="attributes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tab_id", referencedColumnName="id", nullable=false)
     * })
     * @AppORM\Element(sortOrder="3")
     */
    private AttributeTabInterface $tab;

    /**
     * @var AttributeDefInterface
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Product\ProductAttributeDef", fetch="EAGER", inversedBy="attributes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="def_id", referencedColumnName="id", nullable=false)
     * })
     * @AppORM\Element(sortOrder="3")
     */
    private AttributeDefInterface $attributeDef;
}
