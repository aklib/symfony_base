<?php

namespace App\Controller\Admin;

use App\Entity\Product;

/**
 * Class ProductCrudController
 * @package App\Controller\Admin
 *
 * @since: 09.06.2022
 * @author: alexej@kisselev.de
 */
class ProductCrudController extends AbstractAttributableEntityController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }
}
