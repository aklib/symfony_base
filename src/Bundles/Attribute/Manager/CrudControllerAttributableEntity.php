<?php
/**
 * Class CrudControllerAttributableEntity
 * @package App\Bundles\Attribute
 *
 * since: 23.06.2022
 * author: alexej@kisselev.de
 */

namespace App\Bundles\Attribute\Manager;

use App\Entity\Extension\Attributable\CategoryInterface;

interface CrudControllerAttributableEntity
{
    public function getCategory(): CategoryInterface;
}