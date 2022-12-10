<?php /** @noinspection PhpUnused */

/** @noinspection PhpPropertyOnlyWrittenInspection */

namespace App\Entity\Product;

use App\Repository\Product\ProductAttributeOptionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProductAttributeOptionRepository::class)
 */
class ProductAttributeOption
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $val = '';

    /**
     * @ORM\ManyToOne(targetEntity=ProductAttribute::class, inversedBy="productAttributeOptions")
     * @ORM\JoinColumn(nullable=false)
     */
    private ProductAttribute $productAttribute;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVal(): ?string
    {
        return $this->val;
    }

    public function setVal(string $val): self
    {
        $this->val = $val;

        return $this;
    }

    public function getProductAttribute(): ?ProductAttribute
    {
        return $this->productAttribute;
    }

    public function setProductAttribute(?ProductAttribute $productAttribute): self
    {
        $this->productAttribute = $productAttribute;

        return $this;
    }
}
