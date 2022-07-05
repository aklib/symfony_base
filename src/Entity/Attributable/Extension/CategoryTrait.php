<?php /** @noinspection PhpUnused */

/**
 * Class CategoryTrait
 * @package App\Entity\Attributable\Extension
 *
 * since: 04.07.2022
 * author: alexej@kisselev.de
 */

namespace App\Entity\Attributable\Extension;

use App\Entity\Extension\Annotation as AppORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

trait CategoryTrait
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="App\Entity\Generator\SequenceGenerator")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @AppORM\Element(sortOrder="2")
     */
    private string $name;

    /**
     * @ORM\Column(type="string", length=32, unique=true)
     * @AppORM\Element(sortOrder="2")
     *
     */
    private string $uniqueKey;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(type="integer")
     */
    private int $lft;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(type="integer")
     */
    private int $rgt;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     */
    private int $level;

    //============= OVERRIDDEN IN IMPLEMENTATION CLASS =============

    //============= EO OVERRIDDEN =============

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return CategoryInterface
     */
    public function setName(string $name): CategoryInterface
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getUniqueKey(): string
    {
        return $this->uniqueKey;
    }

    /**
     * @param string $uniqueKey
     * @return CategoryInterface
     */
    public function setUniqueKey(string $uniqueKey): CategoryInterface
    {
        $this->uniqueKey = $uniqueKey;
        return $this;
    }

    /**
     * @return int
     */
    public function getLft(): int
    {
        return $this->lft;
    }

    /**
     * @param int $lft
     * @return CategoryInterface
     */
    public function setLft(int $lft): CategoryInterface
    {
        $this->lft = $lft;
        return $this;
    }

    /**
     * @return int
     */
    public function getRgt(): int
    {
        return $this->rgt;
    }

    /**
     * @param int $rgt
     * @return CategoryInterface
     */
    public function setRgt(int $rgt): CategoryInterface
    {
        $this->rgt = $rgt;
        return $this;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @param int $level
     * @return CategoryInterface
     */
    public function setLevel(int $level): CategoryInterface
    {
        $this->level = $level;
        return $this;
    }

    // ======================= METHODS REQUIRED FOR HYDRATION =======================

    private ?Collection $attributesFromTree = null;

    private function getAttributesRecursive(): Collection
    {
        if ($this->attributesFromTree === null) {
            $result = [];
            $parent = $this;
            do {
                foreach ($parent->getAttributes() as $attribute) {
                    if (in_array($attribute, $result, true)) {
                        continue;
                    }
                    $result[] = $attribute;
                }
                $parent = $parent->getParent();
            } while ($parent !== null);
            usort($result, static function ($a, $b) {
                return $a->getSortOrder() > $b->getSortOrder();
            });
            $this->attributesFromTree = new ArrayCollection($result);
        }
        return $this->attributesFromTree;
    }

    public function hasChildren(): bool
    {
        return $this->rgt - $this->lft > 1;
    }

    public function __toString()
    {
        return $this->name ?? 'unknown';
    }
}