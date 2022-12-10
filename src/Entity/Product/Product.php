<?php /** @noinspection PhpUnused */

namespace App\Entity\Product;

use App\Entity\Extension\Annotation as AppORM;
use App\Entity\Extension\Attributable\AttributableEntity;
use App\Entity\Extension\Attributable\AttributableEntityTrait;
use App\Entity\Extension\Attributable\CategoryInterface;
use App\Entity\Extension\Traits\BlameableEntityTrait;
use App\Entity\Extension\Traits\TimestampableEntityTrait;
use App\Repository\Product\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 */
class Product implements AttributableEntity
{
    use AttributableEntityTrait, TimestampableEntityTrait, BlameableEntityTrait;

    /**
     * @ORM\ManyToOne(targetEntity=ProductCategory::class, inversedBy="products", fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     * @AppORM\Element(sortOrder="3")
     *
     */
    private ?CategoryInterface $category = null;

    /**
     * @ORM\OneToMany(targetEntity=ProductAttributeValue::class, mappedBy="product", orphanRemoval=true)
     */
    private Collection $productAttributeValues;

    public function __construct()
    {
        $this->productAttributeValues = new ArrayCollection();
    }

    /**
     * @return Collection<int, ProductAttributeValue>
     */
    public function getProductAttributeValues(): Collection
    {
        return $this->productAttributeValues;
    }

    public function addProductAttributeValue(ProductAttributeValue $productAttributeValue): self
    {
        if (!$this->productAttributeValues->contains($productAttributeValue)) {
            $this->productAttributeValues[] = $productAttributeValue;
            $productAttributeValue->setProduct($this);
        }

        return $this;
    }

    public function removeProductAttributeValue(ProductAttributeValue $productAttributeValue): self
    {
        // set the owning side to null (unless already changed)
        if ($this->productAttributeValues->removeElement($productAttributeValue) && $productAttributeValue->getProduct() === $this) {
            $productAttributeValue->setProduct(null);
        }
        return $this;
    }
}
