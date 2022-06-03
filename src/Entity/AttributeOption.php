<?php /** @noinspection PhpUnused */

namespace App\Entity;

use App\Repository\AttributeOptionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AttributeOptionRepository::class)
 */
class AttributeOption
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     *
     * @ORM\Column(name="name", type="string", length=64, nullable=false)
     */
    private string $name;

    /**
     * @var int
     *
     * @ORM\Column(name="sort_order", type="integer", nullable=false, options={"default"="1"})
     */
    private int $sortOrder = 1;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Attribute",inversedBy="attributeOptions", fetch="EXTRA_LAZY")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="attribute_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    private Attribute $attribute;

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
     * @return Attribute
     */
    public function getAttribute(): Attribute
    {
        return $this->attribute;
    }

    /**
     * @param Attribute $attribute
     */
    public function setAttribute(Attribute $attribute): void
    {
        $this->attribute = $attribute;
    }

    public function __toString()
    {
        return $this->name;
    }
}
