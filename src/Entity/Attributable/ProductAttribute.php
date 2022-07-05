<?php

namespace App\Entity\Attributable;


use App\Entity\Attributable\Extension\AttributeInterface;
use App\Entity\Attributable\Extension\AttributeTrait;
use App\Entity\Attributable\Extension\CategoryInterface;
use App\Entity\Extension\Annotation as AppORM;
use App\Entity\Extension\Traits\BlameableEntityTrait;
use App\Entity\Extension\Traits\TimestampableEntityTrait;
use App\Repository\Attributable\ProductAttributeRepository;
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
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @var CategoryInterface
     * @ORM\ManyToOne(targetEntity=ProductCategory::class, inversedBy="attributes")
     *
     * @ORM\OrderBy({"lft" = "ASC"})
     * @AppORM\Element(sortOrder="3")
     */
    private CategoryInterface $category;

}
