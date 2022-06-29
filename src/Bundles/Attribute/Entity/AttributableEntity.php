<?php

namespace App\Bundles\Attribute\Entity;

use App\Bundles\Attribute\AttributeValueManagerInterface;
use App\Entity\Category;

/**
 * Class AttributeEntityInterface
 *
 * since: 16.09.2021
 * author: alexej@kisselev.de
 */
interface AttributableEntity
{
    public function getId(): int;

    public function getCategory(): ?Category;

    public function setAttributeManager(AttributeValueManagerInterface $manager): void;

    public function getAttributeValues(): array;
}