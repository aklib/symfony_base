<?php
/**
 * Class AttributeEntityManagerInterface
 * @package App\Bundles\Attribute
 *
 * since: 25.06.2022
 * author: alexej@kisselev.de
 */

namespace App\Bundles\Attribute;

use App\Bundles\Attribute\Entity\AttributableEntity;

interface AttributeManagerEntityInterface
{
    public function getAttributeValue(string $uniqueKey, AttributableEntity $entity);
}