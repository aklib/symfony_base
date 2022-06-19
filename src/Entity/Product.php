<?php /** @noinspection PhpUnused */

/** @noinspection PhpUnusedPrivateFieldInspection */

namespace App\Entity;

use App\Entity\Extension\Annotation as AppORM;
use App\Entity\Extension\AttributableEntity;
use App\Entity\Extension\Traits\AttributableEntityTrait;
use App\Entity\Extension\Traits\BlameableEntityTrait;
use App\Entity\Extension\Traits\TimestampableEntityTrait;
use App\Repository\ProductRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Product implements AttributableEntity
{
    use TimestampableEntityTrait, BlameableEntityTrait, AttributableEntityTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="products", fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     * @AppORM\Element(sortOrder="2")
     */
    private ?Category $category = null;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $active = true;

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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    /**
     * @return DateTime|null
     */
    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime|null $createdAt
     */
    public function setCreatedAt(?DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function __toString()
    {
        if($this->category === null){
            return  (string)($this->name  ?? 'unknown');
        }
        return  $this->category->getName() ?? 'unknown';
    }

//    public function toArray(): array
//    {
//        return [
//            'product' => [
//                'id'   => $this->getId(),
//                'name' => $this->name ?? 'unknown'
//            ]
//        ];
//    }
}
