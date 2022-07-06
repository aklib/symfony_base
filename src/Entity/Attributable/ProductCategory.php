<?php /** @noinspection PhpUnused */

namespace App\Entity\Attributable;

use App\Entity\Attributable\Extension\CategoryInterface;
use App\Entity\Attributable\Extension\CategoryTrait;
use App\Entity\Extension\Annotation as AppORM;
use App\Entity\Extension\Traits\BlameableEntityTrait;
use App\Entity\Extension\Traits\TimestampableEntityTrait;
use App\Repository\Attributable\ProductCategoryRepository;
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

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Attributable\Product", mappedBy="category")
     */
    private Collection $products;
}
   