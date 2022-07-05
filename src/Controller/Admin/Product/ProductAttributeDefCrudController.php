<?php

namespace App\Controller\Admin\Product;

use App\Controller\Admin\AbstractAppGrudController;
use App\Entity\Attributable\ProductAttributeDef;

class ProductAttributeDefCrudController extends AbstractAppGrudController
{
    public static function getEntityFqcn(): string
    {
        return ProductAttributeDef::class;
    }

}
