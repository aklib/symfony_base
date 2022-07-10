<?php /** @noinspection ALL */

namespace App\Entity;

use App\Entity\Extension\Annotation as AppORM;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @AppORM\Element(type="email")
     */
    private string $email;

    /**
     * @ORM\Column(type="json")
     */
    private array $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     * @AppORM\Element(type="password")
     */
    private string $password;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $active = true;

    /**
     * @ORM\OneToOne(targetEntity=UserProfile::class, mappedBy="user", cascade={"persist", "remove"})
     */
    private $userProfile;

    /**
     * @ORM\OneToMany(targetEntity=UserViewConfig::class, mappedBy="user")
     */
    private $userViewConfigs;

    public function __construct()
    {
        $this->userViewConfigs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string)$this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string)$this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

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
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }


    public function getUserProfile(): ?UserProfile
    {
        return $this->userProfile;
    }

    public function setUserProfile(?UserProfile $userProfile): self
    {
        // unset the owning side of the relation if necessary
        if ($userProfile === null && $this->userProfile !== null) {
            $this->userProfile->setUser(null);
        }

        // set the owning side of the relation if necessary
        if ($userProfile !== null && $userProfile->getUser() !== $this) {
            $userProfile->setUser($this);
        }

        $this->userProfile = $userProfile;

        return $this;
    }

    public function __toString()
    {
        return $this->email;
    }

    /**
     * Avoid exception "Serialization of 'Closure' is not allowed" because userProfile
     * @return array
     */
    public function __serialize(): array
    {
        return [
            'id'       => $this->id,
            'email'    => $this->email,
            'roles'    => $this->roles,
            'password' => $this->password,
            'active'   => $this->active,
        ];
    }

    /**
     * @return Collection<int, UserViewConfig>
     */
    public function getUserViewConfigs(): Collection
    {
        return $this->userViewConfigs;
    }

    public function addUserViewConfig(UserViewConfig $userViewConfig): self
    {
        if (!$this->userViewConfigs->contains($userViewConfig)) {
            $this->userViewConfigs[] = $userViewConfig;
            $userViewConfig->setUser($this);
        }

        return $this;
    }

    public function removeUserViewConfig(UserViewConfig $userViewConfig): self
    {
        if ($this->userViewConfigs->removeElement($userViewConfig)) {
            // set the owning side to null (unless already changed)
            if ($userViewConfig->getUser() === $this) {
                $userViewConfig->setUser(null);
            }
        }

        return $this;
    }
}
