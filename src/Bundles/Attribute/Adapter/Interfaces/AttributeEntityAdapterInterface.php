<?php
/**
 * Class AttributeManagerEntity
 * @package App\Bundles\Attribute
 *
 * since: 29.06.2022
 * author: alexej@kisselev.de
 */

namespace App\Bundles\Attribute\Adapter\Interfaces;

use App\Entity\Extension\Attributable\AttributableEntity;

interface AttributeEntityAdapterInterface
{
    public function addEntity(AttributableEntity $entity): void;

    public function removeEntity(AttributableEntity $entity): void;

    public function flush(): void;
}