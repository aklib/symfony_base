<?php /** @noinspection PhpPropertyOnlyWrittenInspection */

namespace App\Entity;

use App\Bundles\Attribute\Entity\AttributableEntity;
use App\Bundles\Attribute\Entity\AttributableEntityTrait;
use App\Entity\Extension\Traits\BlameableEntityTrait;
use App\Entity\Extension\Traits\TimestampableEntityTrait;
use App\Repository\UserProfileRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserProfileRepository::class)
 */
class UserProfile implements AttributableEntity
{
    use TimestampableEntityTrait, BlameableEntityTrait, AttributableEntityTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\OneToOne(targetEntity=User::class, inversedBy="userProfile", cascade={"persist", "remove"})
     */
    private ?User $user = null;

    public function getId(): int
    {
        return $this->id;
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

    /** @noinspection PhpUndefinedFieldInspection */
    public function __toString()
    {
        // check from attributes
        if(is_string($this->name)){
            return $this->name . '';
        }
        return 'profile#'. $this->id;
    }
}
