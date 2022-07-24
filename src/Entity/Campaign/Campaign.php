<?php

namespace App\Entity\Campaign;

use App\Entity\Extension\Annotation as AppORM;
use App\Entity\Extension\Attributable\AttributableEntity;
use App\Entity\Extension\Attributable\AttributableEntityTrait;
use App\Entity\Extension\Attributable\CategoryInterface;
use App\Entity\Extension\Traits\BlameableEntityTrait;
use App\Entity\Extension\Traits\TimestampableEntityTrait;
use App\Repository\Campaign\CampaignRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CampaignRepository::class)
 */
class Campaign implements AttributableEntity
{
    use AttributableEntityTrait, TimestampableEntityTrait, BlameableEntityTrait;

    /**
     * @ORM\ManyToOne(targetEntity=CampaignCategory::class, inversedBy="campaigns", fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     * @AppORM\Element(sortOrder="3")
     *
     */
    private ?CategoryInterface $category = null;
}
