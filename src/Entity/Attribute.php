<?php /** @noinspection PhpUnused */

namespace App\Entity;

use App\Entity\Extension\Annotation as AppORM;
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
     * @AppORM\Element(sortOrder="1")
     */
    private int $id;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=32, nullable=false)
     * @AppORM\Element(sortOrder="2")
     */
    private string $name;

    /**
     * @var string
     * @ORM\Column(name="unique_key", type="string", length=32, nullable=false)
     * @AppORM\Element(sortOrder="3")
     */
    private string $uniqueKey;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="attributes")
     *
     * @ORM\OrderBy({"lft" = "ASC"})
     * @AppORM\Element(sortOrder="3")
     */
    private Category $category;

    /**
     * @var AttributeTab
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\AttributeTab", fetch="EXTRA_LAZY")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tab_id", referencedColumnName="id", nullable=false)
     * })
     * @AppORM\Element(sortOrder="3")
     */
    private AttributeTab $tab;

    /**
     * @var AttributeDefinition
     *
     * @ORM\ManyToOne(targetEntity="AttributeDefinition", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="attribute_definition_id", referencedColumnName="id", nullable=false)
     * })
     * @AppORM\Element(sortOrder="3")
     */
    private AttributeDefinition $attributeDefinition;

    /**
     * @var bool
     * @ORM\Column(name="multiple", type="boolean", nullable=false)
     * @AppORM\Element(sortOrder="4")
     */
    private bool $multiple = false;

    /**
     * @var bool
     * @ORM\Column(name="required", type="boolean", nullable=false)
     * @AppORM\Element(sortOrder="5")
     */
    private bool $required;

    /**
     * @var string|null
     * @ORM\Column(name="help_text", type="string", length=128, nullable=true)
     * @AppORM\Element(sortOrder="6")
     */
    private ?string $helpText;

    /**
     *
     * @ORM\OneToMany(targetEntity="App\Entity\AttributeOption", mappedBy="attribute", cascade={"persist","remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"sortOrder" = "ASC"})
     * @AppORM\Element(sortOrder="2")
     */
    protected Collection $attributeOptions;

    /**
     * @var int
     * @ORM\Column(name="sort_order", type="integer", nullable=false, options={"default"="1"})
     * @AppORM\Element(sortOrder="7")
     */
    private int $sortOrder = 100;

    /**
     * Attribute constructor.
     */
    public function __construct()
    {
        $this->attributeOptions = new ArrayCollection();
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
     * @return Category|null
     */
    public function getCategory(): Category
    {
        return $this->category;
    }

    /**
     * @param Category|null $category
     */
    public function setCategory(Category $category): void
    {
        $this->category = $category;
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
     * @return AttributeDefinition
     */
    public function getAttributeDefinition(): AttributeDefinition
    {
        return $this->attributeDefinition;
    }

    /**
     * @param AttributeDefinition $attributeDefinition
     */
    public function setAttributeDefinition(AttributeDefinition $attributeDefinition): void
    {
        $this->attributeDefinition = $attributeDefinition;
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
    public function getUniqueKey(): string
    {
        return $this->uniqueKey;
    }

    /**
     * @param string $uniqueKey
     */
    public function setUniqueKey(string $uniqueKey): void
    {
        $this->uniqueKey = $uniqueKey;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function toMapping(): array
    {
        $mapping = [
            'fieldName' => $this->getUniqueKey(),
            'type'      => $this->getAttributeDefinition()->getType(),
            'scale'     => null,
            'length'    => null,
            'unique'    => !$this->isMultiple(),
            'nullable'  => !$this->isRequired(),
            'precision' => null,
            'element'   => [
                'type'      => $this->getAttributeDefinition()->getType(),
                'tab'       => $this->getAttributeDefinition()->getType(),
                'help'      => $this->getHelpText(),
                'sortOrder' => $this->getSortOrder(),
            ]

        ];
        switch ($this->getAttributeDefinition()->getType()) {
            case 'string':
                $mapping['length'] = 255;
                break;
            case 'text':
                $mapping['length'] = 65535;
                break;
        }
        return $mapping;
    }

    // ======================= METHODS REQUIRED FOR HYDRATION =======================

}
