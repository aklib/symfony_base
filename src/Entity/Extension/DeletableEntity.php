<?php
/**
 * Class DeletableEntity
 * @package App\Entity\Extension
 *
 * since: 05.07.2022
 * author: alexej@kisselev.de
 */

namespace App\Entity\Extension;

interface DeletableEntity
{
    public function isDeletable(): bool;
}