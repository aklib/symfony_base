<?php
/**
 * Class BlameableTrait
 * @package App\Entity\Extension
 *
 * since: 01.06.2022
 * author: alexej@kisselev.de
 */

namespace App\Entity\Extension\Traits;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Entity\Extension\Annotation as AppORM;
use Symfony\Component\Security\Core\User\UserInterface;

trait BlameableEntityTrait
{
    /**
     * @var UserInterface|null
     *
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id")
     * @AppORM\Element(sortOrder="100")
     */
    protected ?UserInterface $createdBy;

    /**
     * @var User|null
     *
     * @Gedmo\Blameable(on="update")
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="updated_by", referencedColumnName="id")
     * @AppORM\Element(sortOrder="101")
     */
    protected ?UserInterface $updatedBy;

    /**
     * @return User|null
     */
    public function getCreatedBy(): ?UserInterface
    {
        return $this->createdBy;
    }

    /**
     * @param User|null $createdBy
     */
    public function setCreatedBy(?UserInterface $createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    /**
     * @return User|null
     */
    public function getUpdatedBy(): ?UserInterface
    {
        return $this->updatedBy;
    }

    /**
     * @param User|null $updatedBy
     */
    public function setUpdatedBy(?UserInterface $updatedBy): void
    {
        $this->updatedBy = $updatedBy;
    }
}