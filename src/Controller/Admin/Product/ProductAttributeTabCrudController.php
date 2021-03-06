<?php

namespace App\Controller\Admin\Product;

use App\Controller\Admin\AbstractAppGrudController;
use App\Entity\Product\ProductAttributeTab;

class ProductAttributeTabCrudController extends AbstractAppGrudController
{
    public static function getEntityFqcn(): string
    {
        return ProductAttributeTab::class;
    }
}
