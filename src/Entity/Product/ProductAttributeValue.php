<?php /** @noinspection PhpUnused */

/** @noinspection PhpPropertyOnlyWrittenInspection */

namespace App\Entity\Product;

use App\Repository\Product\ProductAttributeValueRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProductAttributeValueRepository::class)
 */
class ProductAttributeValue
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="productAttributeValues")
     * @ORM\JoinColumn(nullable=false)
     */
    private Product $product;

    /**
     * @ORM\ManyToOne(targetEntity=ProductAttribute::class, inversedBy="productAttributeValues")
     * @ORM\JoinColumn(nullable=false)
     */
    private ProductAttribute $productAttribute;

    /**
     * @ORM\Column(type="json")
     */
    private array $val = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

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

    public function getVal(): ?array
    {
        return $this->val;
    }

    public function setVal(array $val): self
    {
        $this->val = $val;

        return $this;
    }
}
