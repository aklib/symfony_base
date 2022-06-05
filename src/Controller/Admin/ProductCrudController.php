<?php

namespace App\Controller\Admin;

use App\Entity\Product;

class ProductCrudController extends AbstractAppGrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }
}
