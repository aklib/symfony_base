<?php /** @noinspection PhpUnused */

namespace App\Entity;

use App\Entity\Extension\Traits\BlameableEntityTrait;
use App\Entity\Extension\Traits\TimestampableEntityTrait;
use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @Gedmo\Tree(type="nested")
 * @ORM\Entity(repositoryClass=CategoryRepository::class)
 */
class Category
{
    use TimestampableEntityTrait, BlameableEntityTrait;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\OneToMany(targetEntity=Product::class, mappedBy="category", orphanRemoval=true)
     */
    private Collection $products;

    /**
     * @var Collection|null
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Attribute", inversedBy="categories")
     * @ORM\JoinTable(name="attribute_category_xref",
     *   joinColumns={
     *     @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="attribute_id", referencedColumnName="id")
     *   }
     * )
     * @ORM\OrderBy({"sortOrder" = "ASC"})
     */
    private ?Collection $attributes = null;

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
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?Category $parent = null;

    /**
     * @Gedmo\TreeRoot
     * @ORM\ManyToOne(targetEntity="App\Entity\Category")
     * @ORM\JoinColumn(name="tree_root", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?Category $root;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     */
    private int $level;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Category", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    private Collection $children;
    
    public function __construct()
    {
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
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProducts(Product $products): self
    {
        if (!$this->products->contains($products)) {
            $this->products[] = $products;
            $products->setCategory($this);
        }

        return $this;
    }

    public function removeProducts(Product $products): self
    {
        // set the owning side to null (unless already changed)
        if ($this->products->removeElement($products) && $products->getCategory() === $this) {
            $products->setCategory(null);
        }
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
     */
    public function setLft(int $lft): void
    {
        $this->lft = $lft;
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
     */
    public function setRgt(int $rgt): void
    {
        $this->rgt = $rgt;
    }

    /**
     * @return Category|null
     */
    public function getParent(): ?Category
    {
        return $this->parent;
    }

    /**
     * @param Category|null $parent
     */
    public function setParent(?Category $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * @return Category|null
     */
    public function getRoot(): ?Category
    {
        return $this->root;
    }

    /**
     * @param Category|null $root
     */
    public function setRoot(?Category $root): void
    {
        $this->root = $root;
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
     */
    public function setLevel(int $level): void
    {
        $this->level = $level;
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
     */
    public function setChildren(Collection $children): void
    {
        $this->children = $children;
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return Collection|null
     */
    public function getAttributes(): ?Collection
    {
        return $this->attributes;
    }

    /**
     * @param Collection|null $attributes
     */
    public function setAttributes(?Collection $attributes): void
    {
        $this->attributes = $attributes;
    }
}
