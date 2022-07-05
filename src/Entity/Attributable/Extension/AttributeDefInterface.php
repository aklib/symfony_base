<?php /** @noinspection PhpUnused */

/**
 * Class ProductAttributeDefInterface
 * @package App\Entity\Attributable\Extension
 *
 * since: 04.07.2022
 * author: alexej@kisselev.de
 */

namespace App\Entity\Attributable\Extension;

interface AttributeDefInterface
{
    /**
     * @return int
     */
    public function getId(): int;

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @param string $type
     */
    public function setType(string $type): self;

    /**
     * @return string
     */
    public function getDescription(): string;

    /**
     * @param string $description
     */
    public function setDescription(string $description): self;

    /**
     * @return bool
     */
    public function isCanMultiple(): bool;

    /**
     * @param bool $canMultiple
     * @return $this
     */
    public function setCanMultiple(bool $canMultiple): self;
}