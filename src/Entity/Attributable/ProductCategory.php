<?php

namespace App\Entity\Attributable;

use App\Entity\Attributable\Extension\CategoryInterface;
use App\Entity\Attributable\Extension\CategoryTrait;
use App\Entity\Extension\Annotation as AppORM;
use App\Entity\Extension\Traits\BlameableEntityTrait;
use App\Entity\Extension\Traits\TimestampableEntityTrait;
use App\Repository\Attributable\ProductCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @Gedmo\Tree(type="nested")
 * @ORM\Entity(repositoryClass=ProductCategoryRepository::class)
 * @UniqueEntity(
 *     fields={"uniqueKey"},
 *     message="The name '{{ value }}' is already in use. Please choose any other one."
 * )
 */
class ProductCategory implements CategoryInterface
{
    use CategoryTrait, TimestampableEntityTrait, BlameableEntityTrait;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="App\Entity\Attributable\ProductCategory", inversedBy="children", fetch="EAGER")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     * @AppORM\Element(sortOrder="2")
     */
    private ?CategoryInterface $parent = null;

    /**
     * @Gedmo\TreeRoot
     * @ORM\ManyToOne(targetEntity="App\Entity\Attributable\ProductCategory")
     * @ORM\JoinColumn(name="tree_root", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?CategoryInterface $root;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Attributable\ProductCategory", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    private Collection $children;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Attributable\ProductAttribute", mappedBy="category")
     */
    private Collection $attributes;

    public function __construct()
    {
        $this->attributes = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return CategoryInterface|null
     */
    public function getParent(): ?CategoryInterface
    {
        return $this->parent;
    }

    /**
     * @param CategoryInterface|null $parent
     * @return ProductCategory
     */
    public function setParent(?CategoryInterface $parent): ProductCategory
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @return CategoryInterface|null
     */
    public function getRoot(): ?CategoryInterface
    {
        return $this->root;
    }

    /**
     * @param CategoryInterface|null $root
     * @return ProductCategory
     */
    public function setRoot(?CategoryInterface $root): ProductCategory
    {
        $this->root = $root;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    /**
     * @param Collection $children
     * @return ProductCategory
     */
    public function setChildren(Collection $children): CategoryInterface
    {
        $this->children = $children;
        return $this;
    }

    /**
     * @param bool $recursive
     * @return Collection
     */
    public function getAttributes(bool $recursive = false): Collection
    {
        return $this->attributes;
    }

    /**
     * @param ArrayCollection|Collection $attributes
     * @return ProductCategory
     */
    public function setAttributes($attributes): CategoryInterface
    {
        $this->attributes = $attributes;
        return $this;
    }


    public function isDeletable(): bool
    {
        return $this->getAttributes()->count() === 0;
    }
}
   