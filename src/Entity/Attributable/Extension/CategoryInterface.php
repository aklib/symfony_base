<?php
/**
 * Class CategoryInterface
 * @package App\Entity\Attributable\Extension
 *
 * since: 04.07.2022
 * author: alexej@kisselev.de
 */

namespace App\Entity\Attributable\Extension;

use Doctrine\Common\Collections\Collection;

interface CategoryInterface
{
    /**
     * @return int
     */
    public function getId(): int;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     */
    public function setName(string $name): CategoryInterface;

    /**
     * @return string
     */
    public function getUniqueKey(): string;

    /**
     * @param string $uniqueKey
     * @return CategoryInterface
     */
    public function setUniqueKey(string $uniqueKey): CategoryInterface;

    /**
     * @return int
     */
    public function getLft(): int;

    /**
     * @param int $lft
     */
    public function setLft(int $lft): CategoryInterface;

    /**
     * @return int
     */
    public function getRgt(): int;

    /**
     * @param int $rgt
     */
    public function setRgt(int $rgt): CategoryInterface;

    /**
     * @return CategoryInterface|null
     */
    public function getParent(): ?CategoryInterface;

    /**
     * @param CategoryInterface|null $parent
     */
    public function setParent(?CategoryInterface $parent): CategoryInterface;

    /**
     * @return CategoryInterface|null
     */
    public function getRoot(): ?CategoryInterface;

    /**
     * @param CategoryInterface|null $root
     */
    public function setRoot(?CategoryInterface $root): CategoryInterface;

    /**
     * @return int
     */
    public function getLevel(): int;

    /**
     * @param int $level
     */
    public function setLevel(int $level): CategoryInterface;

    /**
     * @return Collection
     */
    public function getChildren(): Collection;

    /**
     * @param Collection $children
     */
    public function setChildren(Collection $children): CategoryInterface;

// ======================= METHODS REQUIRED FOR HYDRATION =======================

    public function hasChildren(): bool;

    /**
     * @param bool $recursive
     * @return Collection<int, AttributeInterface>
     */
    public function getAttributes(bool $recursive = false): Collection;
}