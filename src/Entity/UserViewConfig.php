<?php

namespace App\Entity;

use App\Entity\Extension\Traits\BlameableEntityTrait;
use App\Entity\Extension\Traits\TimestampableEntityTrait;
use App\Repository\UserViewConfigRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserViewConfigRepository::class)
 */
class UserViewConfig
{
    use TimestampableEntityTrait, BlameableEntityTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private string $name = 'default';

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="userViewConfigs")
     */
    private User $user;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $entityFqcn;

    /**
     * @ORM\Column(type="json")
     */
    private array $columnsVisible = [];

    /**
     * @ORM\Column(type="array")
     */
    private array $columnsHidden = [];

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $current = false;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getEntityFqcn(): ?string
    {
        return $this->entityFqcn;
    }

    public function setEntityFqcn(string $entityFqcn): self
    {
        $this->entityFqcn = $entityFqcn;

        return $this;
    }

    public function getColumnsVisible(): ?array
    {
        return $this->columnsVisible;
    }

    public function setColumnsVisible(array $columnsVisible): self
    {
        $this->columnsVisible = $columnsVisible;

        return $this;
    }

    public function getColumnsHidden(): ?array
    {
        return $this->columnsHidden;
    }

    public function setColumnsHidden(array $columnsHidden): self
    {
        $this->columnsHidden = $columnsHidden;

        return $this;
    }

    public function isCurrent(): ?bool
    {
        return $this->current;
    }

    public function setCurrent(bool $current): self
    {
        $this->current = $current;

        return $this;
    }
}
