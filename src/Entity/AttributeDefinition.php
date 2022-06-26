<?php /** @noinspection PhpUnused */

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

    public function isCanMultiple(): bool
    {
        return $this->canMultiple;
    }

    public function setCanMultiple(bool $canMultiple): self
    {
        $this->canMultiple = $canMultiple;

        return $this;
    }

    public function __toString()
    {
        return $this->type;
    }
}
