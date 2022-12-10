<?php /** @noinspection PhpUnused */

/**
 * Class AttributeInterface
 * @package App\Entity\Attributable\Extension
 *
 * since: 04.07.2022
 * author: alexej@kisselev.de
 */

namespace App\Entity\Extension\Attributable;

use App\Entity\Extension\DeletableEntity;
use Doctrine\Common\Collections\Collection;

interface AttributeInterface extends DeletableEntity
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
    public function setName(string $name): AttributeInterface;

    /**
     * @return CategoryInterface
     */
    public function getCategory(): CategoryInterface;

    /**
     * @param CategoryInterface $category
     */
    public function setCategory(CategoryInterface $category): AttributeInterface;

    /**
     * @return string|null
     */
    public function getHelpText(): ?string;

    /**
     * @param string|null $helpText
     */
    public function setHelpText(?string $helpText): AttributeInterface;

    /**
     * @return bool
     */
    public function isMultiple(): bool;

    /**
     * @param bool $multiple
     */
    public function setMultiple(bool $multiple): AttributeInterface;

    /**
     * @return bool
     */
    public function isRequired(): bool;

    /**
     * @param bool $required
     */
    public function setRequired(bool $required): AttributeInterface;

    /**
     * @return int
     */
    public function getSortOrder(): int;

    /**
     * @param int $sortOrder
     */
    public function setSortOrder(int $sortOrder): AttributeInterface;

    /**
     * @return AttributeTabInterface
     */
    public function getTab(): AttributeTabInterface;

    /**
     * @param AttributeTabInterface $tab
     */
    public function setTab(AttributeTabInterface $tab): AttributeInterface;

    /**
     * @return AttributeDefInterface
     */
    public function getAttributeDef(): AttributeDefInterface;

    /**
     * @param AttributeDefInterface $attributeDefinition
     */
    public function setAttributeDef(AttributeDefInterface $attributeDefinition): AttributeInterface;

    /**
     * @return string
     */
    public function getUniqueKey(): string;

    public function getOptionsArray(): array;

    public function setOptionsArray(array $optionsArray): AttributeInterface;

    public function getAssets(): Collection;
    /**
     * @param string $uniqueKey
     */
    public function setUniqueKey(string $uniqueKey): AttributeInterface;

    public function __toString();

    public function toMapping(): array;
}