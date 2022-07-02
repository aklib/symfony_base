<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

/**
 * Class ProductCrudController
 * @package App\Controller\Admin
 *
 * @since: 09.06.2022
 * @author: alexej@kisselev.de
 */
class ProductCrudController extends AbstractAttributableEntityCrudController
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


