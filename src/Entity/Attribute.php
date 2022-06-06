<?php /** @noinspection PhpUnused */

namespace App\Entity;

use App\Repository\AttributeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AttributeRepository::class)
 */
class Attribute
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @var string
     * @ORM\Column(name="key", type="string", length=32, nullable=false)
     */
    private string $key;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=32, nullable=false)
     */
    private string $name;

    /**
     * @var string|null
     * @ORM\Column(name="help_text", type="string", length=128, nullable=true)
     */
    private ?string $helpText;

    /**
     * @var string|null
     * @ORM\Column(name="placeholder", type="string", length=64, nullable=true)
     */
    private ?string $placeholder;

    /**
     * @var bool
     * @ORM\Column(name="multiple", type="boolean", nullable=false)
     */
    private bool $multiple;

    /**
     * @var bool
     * @ORM\Column(name="required", type="boolean", nullable=false)
     */
    private bool $required;

    /**
     * @var int
     * @ORM\Column(name="sort_order", type="integer", nullable=false, options={"default"="1"})
     */
    private int $sortOrder = 100;

    /**
     * @var AttributeTab
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\AttributeTab", fetch="EXTRA_LAZY")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tab_id", referencedColumnName="id", nullable=false)
     * })
     */
    private AttributeTab $tab;

    /**
     * @var AttributeType
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\AttributeType", fetch="EXTRA_LAZY")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="type_id", referencedColumnName="id", nullable=false)
     * })
     */
    private AttributeType $type;

    /**
     *
     * @ORM\OneToMany(targetEntity="App\Entity\AttributeOption", mappedBy="attribute", cascade={"persist","remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"sortOrder" = "ASC"})
     */
    protected Collection $attributeOptions;

    /**
     * @ORM\ManyToMany(targetEntity=Category::class, inversedBy="attributes")
     */
    private $categories;

    /**
     * Attribute constructor.
     */
    public function __construct()
    {
        $this->attributeOptions = new ArrayCollection();
//        $this->categories = new ArrayCollection();
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

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getHelpText(): ?string
    {
        return $this->helpText;
    }

    /**
     * @param string|null $helpText
     */
    public function setHelpText(?string $helpText): void
    {
        $this->helpText = $helpText;
    }

    /**
     * @return string|null
     */
    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }

    /**
     * @param string|null $placeholder
     */
    public function setPlaceholder(?string $placeholder): void
    {
        $this->placeholder = $placeholder;
    }

    /**
     * @return string|null
     */
    public function getGroupName(): ?string
    {
        return $this->groupName;
    }

    /**
     * @param string|null $groupName
     */
    public function setGroupName(?string $groupName): void
    {
        $this->groupName = $groupName;
    }

    /**
     * @return bool
     */
    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    /**
     * @param bool $multiple
     */
    public function setMultiple(bool $multiple): void
    {
        $this->multiple = $multiple;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @param bool $required
     */
    public function setRequired(bool $required): void
    {
        $this->required = $required;
    }

    /**
     * @return string|null
     */
    public function getPattern(): ?string
    {
        return $this->pattern;
    }

    /**
     * @param string|null $pattern
     */
    public function setPattern(?string $pattern): void
    {
        $this->pattern = $pattern;
    }

    /**
     * @return int
     */
    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    /**
     * @param int $sortOrder
     */
    public function setSortOrder(int $sortOrder): void
    {
        $this->sortOrder = $sortOrder;
    }

//    /**
//     * @return Collection
//     */
//    public function getCategories()
//    {
//        return $this->categories;
//    }
//
//    /**
//     * @param Collection $categories
//     */
//    public function setCategories(Collection $categories): void
//    {
//        $this->categories = $categories;
//    }

    /**
     * @return AttributeTab
     */
    public function getTab(): AttributeTab
    {
        return $this->tab;
    }

    /**
     * @param AttributeTab $tab
     */
    public function setTab(AttributeTab $tab): void
    {
        $this->tab = $tab;
    }

    /**
     * @return AttributeType
     */
    public function getType(): AttributeType
    {
        return $this->type;
    }

    /**
     * @param AttributeType $type
     */
    public function setType(AttributeType $type): void
    {
        $this->type = $type;
    }

    /**
     * @return ArrayCollection|Collection
     */
    public function getAttributeOptions()
    {
        return $this->attributeOptions;
    }

    /**
     * @param ArrayCollection|Collection $attributeOptions
     */
    public function setAttributeOptions($attributeOptions): void
    {
        $this->attributeOptions = $attributeOptions;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    public function __toString()
    {
        return $this->name;
    }

    // ======================= METHODS REQUIRED FOR HYDRATION =======================

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        $this->categories->removeElement($category);

        return $this;
    }

}
