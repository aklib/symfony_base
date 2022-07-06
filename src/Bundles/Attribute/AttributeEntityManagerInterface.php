<?php
/**
 * Class AttributeManagerEntity
 * @package App\Bundles\Attribute
 *
 * since: 29.06.2022
 * author: alexej@kisselev.de
 */

namespace App\Bundles\Attribute;

use App\Entity\Extension\Attributable\AttributableEntity;

interface AttributeEntityManagerInterface
{
    public function addEntity(AttributableEntity $entity): void;

    public function removeEntity(AttributableEntity $entity): void;

    public function flush(): void;
}