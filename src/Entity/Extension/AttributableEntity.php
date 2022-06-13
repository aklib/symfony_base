<?php

namespace App\Entity\Extension;

use App\Entity\Category;
use App\EventSubscriber\AttributeHandler;

/**
 * Class AttributeEntityInterface
 *
 * since: 16.09.2021
 * author: alexej@kisselev.de
 */
interface AttributableEntity extends ElasticaEntity
{
    public function getId(): int;

    public function getCategory(): ?Category;

    public function setAttributeValueHandler(AttributeHandler $aes): void;

    public function getAttributeValues(): array;
}