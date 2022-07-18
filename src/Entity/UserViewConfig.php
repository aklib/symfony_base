<?php

namespace App\Entity;

use App\Entity\Extension\Traits\BlameableEntityTrait;
use App\Entity\Extension\Traits\TimestampableEntityTrait;
use App\Repository\UserViewConfigRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

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
    private UserInterface $user;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $entityFqcn;

    /**
     * @ORM\Column(type="json")
     */
    private array $columnOptions = [];

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

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): self
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

    public function getColumnOptions(): ?array
    {
        return $this->columnOptions;
    }

    public function setColumnOptions(array $columnOptions): self
    {
        $this->columnOptions = $columnOptions;

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
