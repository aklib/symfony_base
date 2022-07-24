<?php

namespace App\Entity;

use App\Entity\Extension\Attributable\AttributeDefInterface;
use App\Repository\AttributeDefinitionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AttributeDefinitionRepository::class)
 */
class AttributeDefinition implements AttributeDefInterface
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

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return AttributeDefinition
     */
    public function setId(int $id): AttributeDefInterface
    {
        $this->id = $id;
        return $this;
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

    public function isDeletable(): bool
    {
        return false;
    }
}
