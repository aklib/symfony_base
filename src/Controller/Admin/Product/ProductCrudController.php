<?php

namespace App\Controller\Admin\Product;

use App\Controller\Admin\AbstractAttributableEntityController;
use App\Entity\Product\Product;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

class ProductCrudController extends AbstractAttributableEntityController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)->setPaginatorPageSize(50);
    }
}
