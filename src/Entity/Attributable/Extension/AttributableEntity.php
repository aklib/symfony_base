<?php

namespace App\Entity\Attributable\Extension;

use App\Bundles\Attribute\AttributeValueManagerInterface;

/**
 * Class AttributeEntityInterface
 *
 * since: 16.09.2021
 * author: alexej@kisselev.de
 */
interface AttributableEntity
{
    public function getId(): int;

    public function getName(): string;

    public function setName(string $name): AttributableEntity;

    public function getCategory(): ?CategoryInterface;

    public function setAttributeManager(AttributeValueManagerInterface $manager): AttributableEntity;

    public function getAttributeValues(): array;

    public function setAttributeValues(array $attributeValues = null): AttributableEntity;

    public function createDocData(AttributeInterface $attribute = null): ?array;

    public function updateDocData(array &$docData, AttributeInterface $attribute = null): bool;

    public function getScope(): string;

    public function getAttributeManager(): AttributeValueManagerInterface;
}