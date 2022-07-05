<?php /** @noinspection PhpUnused */

/**
 * Class AttributeTabTrait
 * @package App\Entity\Attributable\Extension
 *
 * since: 04.07.2022
 * author: alexej@kisselev.de
 */

namespace App\Entity\Attributable\Extension;

use Doctrine\ORM\Mapping as ORM;

trait AttributeTabTrait
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @var string
     *
     * @ORM\Column(name="label", type="string", length=64, nullable=false)
     */
    private string $name;

    /**
     * @var int
     *
     * @ORM\Column(name="sort_order", type="integer", nullable=false, options={"default"="1"})
     */
    private int $sortOrder = 1;

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
     * @return AttributeTabInterface
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return int
     */
    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    /**
     * @param int $sortOrder
     * @return AttributeTabInterface
     */
    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }
}