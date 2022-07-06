<?php /** @noinspection PhpUnused */

/**
 * Class CategoryTrait
 * @package App\Entity\Attributable\Extension
 *
 * since: 04.07.2022
 * author: alexej@kisselev.de
 */

namespace App\Entity\Attributable\Extension;

use App\Entity\Attributable\ProductCategory;
use App\Entity\Extension\Annotation as AppORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

trait CategoryTrait
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="App\Entity\Generator\SequenceGenerator")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @AppORM\Element(sortOrder="2")
     */
    private string $name;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(type="integer")
     */
    private int $lft;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(type="integer")
     */
    private int $rgt;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     */
    private int $level;

    //============= OVERRIDDEN IN IMPLEMENTATION CLASS =============
    // must be override because target entity

    private ?CategoryInterface $parent = null;
    private ?CategoryInterface $root;
    private Collection $children;
    private Collection $attributes;
    private Collection $products;

    //============= EO OVERRIDDEN =============

    public function __construct()
    {
        $this->attributes = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->products = new ArrayCollection();
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
     * @return CategoryInterface
     */
    public function setName(string $name): CategoryInterface
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return int
     */
    public function getLft(): int
    {
        return $this->lft;
    }

    /**
     * @param int $lft
     * @return CategoryInterface
     */
    public function setLft(int $lft): CategoryInterface
    {
        $this->lft = $lft;
        return $this;
    }

    /**
     * @return int
     */
    public function getRgt(): int
    {
        return $this->rgt;
    }

    /**
     * @param int $rgt
     * @return CategoryInterface
     */
    public function setRgt(int $rgt): CategoryInterface
    {
        $this->rgt = $rgt;
        return $this;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @param int $level
     * @return CategoryInterface
     */
    public function setLevel(int $level): CategoryInterface
    {
        $this->level = $level;
        return $this;
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
     * @return Collection
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    /**
     * @param Collection $products
     * @return ProductCategory
     */
    public function setProducts(Collection $products): ProductCategory
    {
        $this->products = $products;
        return $this;
    }

    public function isDeletable(): bool
    {
        return $this->getAttributes()->count() === 0 && $this->getProducts()->count() === 0;
    }

    // ======================= METHODS REQUIRED FOR HYDRATION =======================

    /**
     * @param bool $recursive
     * @return Collection
     */
    public function getAttributes(bool $recursive = false): Collection
    {
        if ($recursive) {
            return $this->getAttributesRecursive();
        }
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

    private ?Collection $attributesFromTree = null;

    private function getAttributesRecursive(): Collection
    {
        if ($this->attributesFromTree === null) {
            $result = [];
            $parent = $this;
            do {
                foreach ($parent->getAttributes() as $attribute) {
                    if (in_array($attribute, $result, true)) {
                        continue;
                    }
                    $result[] = $attribute;
                }
                $parent = $parent->getParent();
            } while ($parent !== null);
            usort($result, static function ($a, $b) {
                return $a->getSortOrder() > $b->getSortOrder();
            });
            $this->attributesFromTree = new ArrayCollection($result);
        }
        return $this->attributesFromTree;
    }

    public function hasChildren(): bool
    {
        return $this->rgt - $this->lft > 1;
    }


    public function __toString()
    {
        return $this->name ?? 'unknown';
    }
}