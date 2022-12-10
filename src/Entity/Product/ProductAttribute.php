<?php /** @noinspection PhpUnused */

namespace App\Entity\Product;


use App\Entity\Extension\Annotation as AppORM;
use App\Entity\Extension\Attributable\AttributeDefInterface;
use App\Entity\Extension\Attributable\AttributeInterface;
use App\Entity\Extension\Attributable\AttributeTabInterface;
use App\Entity\Extension\Attributable\AttributeTrait;
use App\Entity\Extension\Attributable\CategoryInterface;
use App\Entity\Extension\Traits\BlameableEntityTrait;
use App\Entity\Extension\Traits\TimestampableEntityTrait;
use App\Repository\Product\ProductAttributeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=ProductAttributeRepository::class)
 * @UniqueEntity(
 *     fields={"uniqueKey"},
 *     message="The name '{{ value }}' is already in use. Please choose any other one."
 * )
 */
class ProductAttribute implements AttributeInterface
{

    use AttributeTrait, TimestampableEntityTrait, BlameableEntityTrait;

    /**
     * @var CategoryInterface
     * @ORM\ManyToOne(targetEntity=ProductCategory::class, inversedBy="attributes")
     *
     * @ORM\OrderBy({"lft" = "ASC"})
     * @AppORM\Element(sortOrder="3")
     */
    private CategoryInterface $category;

    /**
     * @var AttributeTabInterface
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Product\ProductAttributeTab", fetch="EXTRA_LAZY", inversedBy="attributes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tab_id", referencedColumnName="id", nullable=false)
     * })
     * @AppORM\Element(sortOrder="3")
     */
    private AttributeTabInterface $tab;

    /**
     * @var AttributeDefInterface
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\AttributeDefinition", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="def_id", referencedColumnName="id", nullable=false)
     * })
     * @AppORM\Element(sortOrder="3")
     */
    private AttributeDefInterface $attributeDef;

    /**
     * @ORM\OneToMany(targetEntity=ProductAttributeAsset::class, mappedBy="attribute", orphanRemoval=true, cascade={"persist", "remove"})
     */
    private Collection $assets;

    /**
     * @ORM\OneToMany(targetEntity=ProductAttributeOption::class, mappedBy="productAttribute", orphanRemoval=true)
     */
    private $productAttributeOptions;

    /**
     * @ORM\OneToMany(targetEntity=ProductAttributeValue::class, mappedBy="productAttribute", orphanRemoval=true)
     */
    private $productAttributeValues;

    public function __construct()
    {
        $this->assets = new ArrayCollection();
        $this->productAttributeOptions = new ArrayCollection();
        $this->productAttributeValues = new ArrayCollection();
    }

    /**
     * @return Collection<int, ProductAttributeAsset>
     */
    public function getAssets(): Collection
    {
        return $this->assets;
    }

    public function addAsset(ProductAttributeAsset $asset): self
    {
        if (!$this->assets->contains($asset)) {
            $this->assets[] = $asset;
            $asset->setAttribute($this);
        }

        return $this;
    }

    public function removeAsset(ProductAttributeAsset $asset): self
    {
        // set the owning side to null (unless already changed)
        if ($this->assets->removeElement($asset) && $asset->getAttribute() === $this) {
            $asset->setAttribute(null);
        }
        return $this;
    }

    /**
     * @return Collection<int, ProductAttributeOption>
     */
    public function getProductAttributeOptions(): Collection
    {
        return $this->productAttributeOptions;
    }

    public function addProductAttributeOption(ProductAttributeOption $productAttributeOption): self
    {
        if (!$this->productAttributeOptions->contains($productAttributeOption)) {
            $this->productAttributeOptions[] = $productAttributeOption;
            $productAttributeOption->setProductAttribute($this);
        }

        return $this;
    }

    public function removeProductAttributeOption(ProductAttributeOption $productAttributeOption): self
    {
        if ($this->productAttributeOptions->removeElement($productAttributeOption)) {
            // set the owning side to null (unless already changed)
            if ($productAttributeOption->getProductAttribute() === $this) {
                $productAttributeOption->setProductAttribute(null);
            }
        }

        return $this;
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
            $productAttributeValue->setProductAttribute($this);
        }

        return $this;
    }

    public function removeProductAttributeValue(ProductAttributeValue $productAttributeValue): self
    {
        if ($this->productAttributeValues->removeElement($productAttributeValue)) {
            // set the owning side to null (unless already changed)
            if ($productAttributeValue->getProductAttribute() === $this) {
                $productAttributeValue->setProductAttribute(null);
            }
        }

        return $this;
    }
}
