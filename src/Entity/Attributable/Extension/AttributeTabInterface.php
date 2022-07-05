<?php
/**
 * Class AttributeTabInterface
 * @package App\Entity\Attributable\Extension
 *
 * since: 04.07.2022
 * author: alexej@kisselev.de
 */

namespace App\Entity\Attributable\Extension;

interface AttributeTabInterface
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
    public function setName(string $name): self;

    /**
     * @return int
     */
    public function getSortOrder(): int;

    /**
     * @param int $sortOrder
     */
    public function setSortOrder(int $sortOrder): self;

    public function __toString();
}