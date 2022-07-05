<?php /** @noinspection PhpUnused */

/**
 * Class ProductAttributeDefTrait
 * @package App\Entity\Attributable\Extension
 *
 * since: 04.07.2022
 * author: alexej@kisselev.de
 */

namespace App\Entity\Attributable\Extension;

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
    public function setType(string $type): self
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
    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
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