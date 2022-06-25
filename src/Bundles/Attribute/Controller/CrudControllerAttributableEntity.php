<?php
/**
 * Class AttributableEntityController
 * @package App\Bundles\Attribute
 *
 * since: 23.06.2022
 * author: alexej@kisselev.de
 */

namespace App\Bundles\Attribute\Controller;

use App\Entity\Category;

interface CrudControllerAttributableEntity
{
    public function getCategory(): ?Category;
}