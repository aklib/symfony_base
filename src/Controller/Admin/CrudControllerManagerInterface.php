<?php
/**
 * Class CrudControllerManagerInterface
 * @package App\Controller\Admin
 *
 * since: 18.06.2022
 * author: alexej@kisselev.de
 */

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Controller\CrudControllerInterface;

interface CrudControllerManagerInterface extends CrudControllerInterface
{
    public function isVisibleProperty(string $propertyName, string $pageName = null): bool;
}