<?php

namespace App\Controller\Admin\Product;

use App\Bundles\Attribute\Constant;
use App\Controller\Admin\AbstractCategoryCrudController;
use App\Entity\Product\ProductCategory;

class ProductCategoryCrudController extends AbstractCategoryCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProductCategory::class;
    }

    public function getFieldOptions(string $pageName = null): array
    {
        $fields = parent::getFieldOptions($pageName);
        if ($pageName !== 'index') {
            $fields['products'][Constant::OPTION_VISIBLE] = false;
        }
        return $fields;
    }
}
