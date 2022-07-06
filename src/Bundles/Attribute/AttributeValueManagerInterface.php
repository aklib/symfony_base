<?php
/**
 * Class AttributeManagerElastica
 * @package App\Bundles\Attribute
 *
 * since: 29.06.2022
 * author: alexej@kisselev.de
 */

namespace App\Bundles\Attribute;

use App\Entity\Extension\Attributable\AttributableEntity;

interface AttributeValueManagerInterface
{
    public function getAttributeValues(AttributableEntity $entity): array;

    public function convertValue(string $uniqueKey, $attributeValue, bool $download = true);
}