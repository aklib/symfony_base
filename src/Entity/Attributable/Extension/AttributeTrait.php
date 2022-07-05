<?php /** @noinspection PhpUnused */

/**
 * Class AttributeTrait
 * @package App\Entity\Attributable\Extension
 *
 * since: 04.07.2022
 * author: alexej@kisselev.de
 */

namespace App\Entity\Attributable\Extension;

use App\Entity\Extension\Annotation as AppORM;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait AttributeTrait
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="App\Entity\Generator\SequenceGenerator")
     * @ORM\Column(type="integer")
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
     * @ORM\Column(name="unique_key", type="string", length=32, nullable=false, unique=true)
     * @AppORM\Element(sortOrder="3", help="Only [a-z] characters in lower case and underscore '_'.")
     * @Assert\Regex(
     *     pattern     = "/^[a-z_]+$/"
     * )
     * @Assert\NotEqualTo("category")
     * @Assert\NotEqualTo("id")
     * @Assert\NotEqualTo("active")
     */
    private string $uniqueKey;

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
     * @var int
     * @ORM\Column(name="sort_order", type="integer", nullable=false, options={"default"="1"})
     * @AppORM\Element(sortOrder="7")
     */
    private int $sortOrder = 100;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private array $optionsArray = [];

    private CategoryInterface $category;

    private AttributeTabInterface $tab;

    private AttributeDefInterface $attributeDef;

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
     * @return AttributeInterface
     */
    public function setName(string $name): AttributeInterface
    {
        $this->name = $name;
        return $this;
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
     * @return AttributeInterface
     */
    public function setUniqueKey(string $uniqueKey): AttributeInterface
    {
        $this->uniqueKey = $uniqueKey;
        return $this;
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
     * @return AttributeInterface
     */
    public function setMultiple(bool $multiple): AttributeInterface
    {
        $this->multiple = $multiple;
        return $this;
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
     * @return AttributeInterface
     */
    public function setRequired(bool $required): AttributeInterface
    {
        $this->required = $required;
        return $this;
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
     * @return AttributeInterface
     */
    public function setHelpText(?string $helpText): AttributeInterface
    {
        $this->helpText = $helpText;
        return $this;
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
     * @return AttributeInterface
     */
    public function setSortOrder(int $sortOrder): AttributeInterface
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }

    /**
     * @return array
     */
    public function getOptionsArray(): array
    {
        return $this->optionsArray;
    }

    /**
     * @param array $optionsArray
     * @return AttributeInterface
     */
    public function setOptionsArray(array $optionsArray): AttributeInterface
    {
        $this->optionsArray = $optionsArray;
        return $this;
    }

    /**
     * @return CategoryInterface
     */
    public function getCategory(): CategoryInterface
    {
        return $this->category;
    }

    /**
     * @param CategoryInterface $category
     * @return AttributeInterface
     */
    public function setCategory(CategoryInterface $category): AttributeInterface
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return AttributeTabInterface
     */
    public function getTab(): AttributeTabInterface
    {
        return $this->tab;
    }

    /**
     * @param AttributeTabInterface $tab
     * @return AttributeInterface
     */
    public function setTab(AttributeTabInterface $tab): AttributeInterface
    {
        $this->tab = $tab;
        return $this;
    }

    /**
     * @return AttributeDefInterface
     */
    public function getAttributeDef(): AttributeDefInterface
    {
        return $this->attributeDef;
    }

    /**
     * @param AttributeDefInterface $attributeDef
     * @return AttributeInterface
     */
    public function setAttributeDef(AttributeDefInterface $attributeDef): AttributeInterface
    {
        $this->attributeDef = $attributeDef;
        return $this;
    }

// ======================= HELP METHODS REQUIRED  =======================
    public function toMapping(): array
    {
        $mapping = [
            'fieldName' => $this->getUniqueKey(),
            'type'      => $this->getAttributeDef()->getType(),
            'scale'     => null,
            'length'    => null,
            'unique'    => !$this->isMultiple(),
            'nullable'  => !$this->isRequired(),
            'precision' => null,
            'element'   => [
                'type'      => $this->getAttributeDef()->getType(),
                'tab'       => $this->getAttributeDef()->getType(),
                'help'      => $this->getHelpText(),
                'sortOrder' => $this->getSortOrder(),
            ]

        ];
        switch ($this->getAttributeDef()->getType()) {
            case 'string':
                $mapping['length'] = 255;
                break;
            case 'text':
                $mapping['length'] = 65535;
                break;
        }
        return $mapping;
    }

    public function __toString()
    {
        return $this->name;
    }

}