<?php

namespace App\Entity\Extension;

use Doctrine\Common\Collections\Collection;

/**
 * Class AttributeEntityInterface
 *
 * since: 16.09.2021
 * author: alexej@kisselev.de
 */
interface AttributableEntity extends ElasticaEntity
{
    public function getAttributes(): Collection;

    public function getAttributeValues(): array;

    public function setAttributeValues(array $attributeValues): void;


}