<?php

namespace App\Entity\Campaign;

use App\Entity\Extension\Attributable\AttributeTabInterface;
use App\Entity\Extension\Attributable\AttributeTabTrait;
use App\Entity\Extension\Traits\BlameableEntityTrait;
use App\Entity\Extension\Traits\TimestampableEntityTrait;
use App\Repository\Campaign\CampaignAttributeTabRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CampaignAttributeTabRepository::class)
 */
class CampaignAttributeTab implements AttributeTabInterface
{
    use AttributeTabTrait, TimestampableEntityTrait, BlameableEntityTrait;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Campaign\CampaignAttribute", mappedBy="tab")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private Collection $attributes;
}
