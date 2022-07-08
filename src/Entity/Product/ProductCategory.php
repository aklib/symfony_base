<?php /** @noinspection PhpUnused */

namespace App\Entity\Product;

use App\Entity\Extension\Annotation as AppORM;
use App\Entity\Extension\Attributable\CategoryInterface;
use App\Entity\Extension\Attributable\CategoryTrait;
use App\Entity\Extension\Traits\BlameableEntityTrait;
use App\Entity\Extension\Traits\TimestampableEntityTrait;
use App\Repository\Product\ProductCategoryRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @Gedmo\Tree(type="nested")
 * @ORM\Entity(repositoryClass=ProductCategoryRepository::class)
 */
class ProductCategory implements CategoryInterface
{
    use CategoryTrait, TimestampableEntityTrait, BlameableEntityTrait;

    /**
     * Make first because a tree view
     * @ORM\Column(type="string", length=32)
     * @AppORM\Element(sortOrder="0")
     */
    private string $name;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="App\Entity\Product\ProductCategory", inversedBy="children", fetch="EAGER")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     * @AppORM\Element(sortOrder="2")
     */
    private ?CategoryInterface $parent = null;

    /**
     * @Gedmo\TreeRoot
     * @ORM\ManyToOne(targetEntity="App\Entity\Product\ProductCategory")
     * @ORM\JoinColumn(name="tree_root", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?CategoryInterface $root;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Product\ProductCategory", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    private Collection $children;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Product\ProductAttribute", mappedBy="category")
     */
    private Collection $attributes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Product\Product", mappedBy="category")
     */
    private Collection $products;
}
   