<?php /** @noinspection PhpUnused */

namespace App\Entity\Campaign;

use App\Entity\Extension\Annotation as AppORM;
use App\Entity\Extension\Attributable\AttributableEntity;
use App\Entity\Extension\Attributable\CategoryInterface;
use App\Entity\Extension\Attributable\CategoryTrait;
use App\Entity\Extension\Traits\BlameableEntityTrait;
use App\Entity\Extension\Traits\TimestampableEntityTrait;
use App\Repository\Campaign\CampaignCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @Gedmo\Tree(type="nested")
 * @ORM\Entity(repositoryClass=CampaignCategoryRepository::class)
 */
class CampaignCategory implements CategoryInterface
{
    use CategoryTrait, TimestampableEntityTrait, BlameableEntityTrait;

    /**
     * Make first because a tree view
     * @ORM\Column(type="string", length=32)
     * @AppORM\Element(sortOrder="0")
     */
    private string $name;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="App\Entity\Campaign\CampaignCategory", inversedBy="children", fetch="EAGER")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     * @AppORM\Element(sortOrder="2")
     */
    private ?CategoryInterface $parent = null;

    /**
     * @Gedmo\TreeRoot
     * @ORM\ManyToOne(targetEntity="App\Entity\Campaign\CampaignCategory")
     * @ORM\JoinColumn(name="tree_root", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?CategoryInterface $root;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Campaign\CampaignCategory", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    private Collection $children;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Campaign\CampaignAttribute", mappedBy="category")
     */
    private Collection $attributes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Campaign\Campaign", mappedBy="category")
     */
    private Collection $campaigns;

    /**
     * @return Collection<AttributableEntity>
     */
    public function getCampaigns(): Collection
    {
        return $this->campaigns;
    }

    /**
     * @param Collection<AttributableEntity> $campaigns
     * @return CampaignCategory
     */
    public function setCampaigns(Collection $campaigns): CampaignCategory
    {
        $this->campaigns = $campaigns;
        return $this;
    }

    public function isDeletable(): bool
    {
        return $this->getAttributes()->count() === 0 && $this->getCampaigns()->count() === 0;
    }

    public function __construct()
    {
        $this->attributes = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->campaigns = new ArrayCollection();
    }


}
   