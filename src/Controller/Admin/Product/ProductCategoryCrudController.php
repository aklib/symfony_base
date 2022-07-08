<?php

namespace App\Controller\Admin\Product;

use App\Controller\Admin\AbstractCategoryCrudController;
use App\Entity\Product\ProductCategory;

class ProductCategoryCrudController extends AbstractCategoryCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProductCategory::class;
    }
}
