<?php /** @noinspection PhpUnused */

namespace App\Entity;

use App\Entity\Extension\Annotation as AppORM;
use App\Entity\Extension\Traits\BlameableEntityTrait;
use App\Entity\Extension\Traits\TimestampableEntityTrait;
use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @Gedmo\Tree(type="nested")
 * @ORM\Entity(repositoryClass=CategoryRepository::class)
 * @UniqueEntity(
 *     fields={"uniqueKey"},
 *     message="The name '{{ value }}' is already in use. Please choose any other one."
 * )
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
     * @AppORM\Element(sortOrder="2")
     */
    private string $name;

    /**
     * @ORM\Column(type="string", length=32, unique=true)
     * @AppORM\Element(sortOrder="2")
     *
     */
    private string $uniqueKey;

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
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="children", fetch="EAGER")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     * @AppORM\Element(sortOrder="2")
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

    /**
     * @ORM\OneToMany(targetEntity=Attribute::class, mappedBy="category", fetch="EAGER")
     * @ORM\OrderBy({"sortOrder" = "ASC"})
     * @AppORM\Element(sortOrder="2")
     */
    private Collection $attributes;

    public function __construct()
    {
        $this->attributes = new ArrayCollection();
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

    public function getUniqueKey(): ?string
    {
        return $this->uniqueKey;
    }

    public function setUniqueKey(string $uniqueKey): self
    {
        $this->uniqueKey = $uniqueKey;

        return $this;
    }

    // ======================= NESTED SET =======================

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

    // ======================= METHODS REQUIRED FOR HYDRATION =======================

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

    public function resetAttributesRecursive(): void
    {
        $this->attributesFromTree = null;
    }

    public function hasChildren(): bool
    {
        return $this->rgt - $this->lft > 1;
    }

    /**
     * @param bool $recursive
     * @return Collection<int, Attribute>
     */
    public function getAttributes(bool $recursive = false): Collection
    {
        if ($recursive) {
            return $this->getAttributesRecursive();
        }
        return $this->attributes;
    }
}
   