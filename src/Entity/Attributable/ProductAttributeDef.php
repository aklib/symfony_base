<?php

namespace App\Entity\Attributable;

use App\Entity\Attributable\Extension\AttributeDefInterface;
use App\Entity\Attributable\Extension\AttributeDefTrait;
use App\Entity\Extension\Traits\BlameableEntityTrait;
use App\Entity\Extension\Traits\TimestampableEntityTrait;
use App\Repository\Attributable\ProductAttributeDefRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProductAttributeDefRepository::class)
 */
class ProductAttributeDef implements AttributeDefInterface
{
    use AttributeDefTrait, TimestampableEntityTrait, BlameableEntityTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;
}
