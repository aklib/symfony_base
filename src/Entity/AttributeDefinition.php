<?php

namespace App\Entity;

use App\Repository\AttributeDefinitionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AttributeDefinitionRepository::class)
 */
class AttributeDefinition
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;
    /**
     *
     * @ORM\Column(name="type", type="string", length=16, nullable=false)
     */
    private string $type;

    /**
     *
     * @ORM\Column(name="related_class", type="string", length=128, nullable=true)
     */
    private ?string $relatedClass = null;
    /**
     *
     * @ORM\Column(name="element", type="string", length=16, nullable=false, options={"default"="text"})
     */
    private string $element = 'text';

    /**
     *
     * @ORM\Column(name="description", type="string", length=128, nullable=true)
     */
    private string $description;

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
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getElement(): string
    {
        return $this->element;
    }

    /**
     * @param string $element
     */
    public function setElement(string $element): void
    {
        $this->element = $element;
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
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string|null
     */
    public function getRelatedClass(): ?string
    {
        return $this->relatedClass;
    }

    /**
     * @param string|null $relatedClass
     */
    public function setRelatedClass(?string $relatedClass): void
    {
        $this->relatedClass = $relatedClass;
    }

    public function __toString()
    {
        return $this->type;
    }
}
