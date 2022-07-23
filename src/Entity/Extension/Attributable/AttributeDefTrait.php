<?php /** @noinspection PhpUnused */

/**
 * Class ProductAttributeDefTrait
 * @package App\Entity\Attributable\Extension
 *
 * since: 04.07.2022
 * author: alexej@kisselev.de
 */

namespace App\Entity\Extension\Attributable;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

trait AttributeDefTrait
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     *
     * @ORM\Column(name="type", type="string", length=16, nullable=false, unique=true)
     */
    private string $type;

    /**
     *
     * @ORM\Column(name="description", type="string", length=128, nullable=true)
     */
    private string $description;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $canMultiple = false;

    private Collection $attributes;

    public function __construct()
    {
        $this->attributes = new ArrayCollection();
    }

    /**
     * @return Collection
     */
    public function getAttributes(): Collection
    {
        return $this->attributes;
    }

    /**
     * @param ArrayCollection|Collection $attributes
     * @return AttributeDefInterface
     */
    public function setAttributes($attributes): AttributeDefInterface
    {
        $this->attributes = $attributes;
        return $this;
    }

    public function isDeletable(): bool
    {
        return $this->getAttributes()->count() === 0;
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
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return AttributeDefInterface
     */
    public function setType(string $type): AttributeDefInterface
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return AttributeDefInterface
     */
    public function setDescription(string $description): AttributeDefInterface
    {
        $this->description = $description;
        return $this;
    }

    public function isCanMultiple(): bool
    {
        return $this->canMultiple;
    }

    public function setCanMultiple(bool $canMultiple): AttributeDefInterface
    {
        $this->canMultiple = $canMultiple;

        return $this;
    }

    public function __toString()
    {
        return $this->type;
    }
}