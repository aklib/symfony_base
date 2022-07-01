<?php /** @noinspection PhpUnused */

namespace App\Entity;

use App\Entity\Extension\Traits\BlameableEntityTrait;
use App\Entity\Extension\Traits\TimestampableEntityTrait;
use App\Repository\AttributeValueRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AttributeValueRepository::class)
 */
class AttributeValue
{
    use TimestampableEntityTrait, BlameableEntityTrait;

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
     * @ORM\Column(type="integer")
     */
    private int $attributableId;

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

    public function getDocData(): ?array
    {
        return $this->docData;
    }

    public function setDocData(?array $docData): self
    {
        $this->docData = $docData;

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
}
