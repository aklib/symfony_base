<?php /** @noinspection PhpUnused */

namespace App\Entity;

use App\Entity\Extension\Traits\TimestampableEntityTrait;
use App\Repository\AttributeValueRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="attribute_value", uniqueConstraints={@ORM\UniqueConstraint(name="attributable_id_scope", columns={"attributable_id", "scope"})})
 * @ORM\Entity(repositoryClass=AttributeValueRepository::class)
 */
class AttributeValue
{
    use TimestampableEntityTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=16)
     */
    private string $scope;

    /**
     * @ORM\Column(type="json")
     */
    private array $docData = [];

    /**
     * @ORM\Column(type="text")
     */
    private string $tags = '';

    /**
     * @ORM\Column(type="integer")
     */
    private int $attributableId;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    protected string $createdBy;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    protected string $updatedBy;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }

    public function setScope(string $scope): self
    {
        $this->scope = $scope;

        return $this;
    }

    public function getDocData(): array
    {
        return $this->docData;
    }

    public function setDocData(array $docData): self
    {
        $this->docData = $docData;

        return $this;
    }

    /**
     * @return string
     */
    public function getTags(): string
    {
        return $this->tags;
    }

    /**
     * @param string $tags
     * @return AttributeValue
     */
    public function setTags(string $tags): AttributeValue
    {
        $this->tags = $tags;
        return $this;
    }

    public function getAttributableId(): int
    {
        return $this->attributableId;
    }

    public function setAttributableId(int $attributableId): self
    {
        $this->attributableId = $attributableId;

        return $this;
    }

    /**
     * @return string
     */
    public function getCreatedBy(): string
    {
        return $this->createdBy;
    }

    /**
     * @param string $createdBy
     */
    public function setCreatedBy(string $createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    /**
     * @return string
     */
    public function getUpdatedBy(): string
    {
        return $this->updatedBy;
    }

    /**
     * @param string $updatedBy
     */
    public function setUpdatedBy(string $updatedBy): void
    {
        $this->updatedBy = $updatedBy;
    }
}
