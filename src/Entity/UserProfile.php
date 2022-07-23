<?php /** @noinspection PhpPropertyOnlyWrittenInspection */

namespace App\Entity;

use App\Entity\Extension\Attributable\AttributableEntityTrait;
use App\Entity\Extension\Traits\BlameableEntityTrait;
use App\Entity\Extension\Traits\TimestampableEntityTrait;
use App\Repository\UserProfileRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserProfileRepository::class)
 */
class UserProfile implements Extension\Attributable\AttributableEntity
{
    use TimestampableEntityTrait, BlameableEntityTrait, AttributableEntityTrait;

    /**
     * @ORM\OneToOne(targetEntity=User::class, inversedBy="userProfile", cascade={"persist", "remove"})
     */
    private ?User $user = null;

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function __toString()
    {
        return 'profile#' . $this->id;
    }

    public function isDeletable(): bool
    {
        return false;
    }
}
