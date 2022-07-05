<?php

namespace App\Entity\Attributable;

use App\Entity\Attributable\Extension\AttributableEntity;
use App\Entity\Attributable\Extension\AttributableEntityTrait;
use App\Entity\Attributable\Extension\CategoryInterface;
use App\Entity\Extension\Annotation as AppORM;
use App\Entity\Extension\Traits\BlameableEntityTrait;
use App\Entity\Extension\Traits\TimestampableEntityTrait;
use App\Repository\Attributable\ProductRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 */
class Product implements AttributableEntity
{
    use AttributableEntityTrait, TimestampableEntityTrait, BlameableEntityTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity=ProductCategory::class, fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     * @AppORM\Element(sortOrder="3")
     *
     */
    private ?CategoryInterface $category = null;

    public function getId(): int
    {
        return $this->id;
    }
}
