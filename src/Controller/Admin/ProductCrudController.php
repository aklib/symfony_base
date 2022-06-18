<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

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

    public function configureCrud(Crud $crud): Crud
    {
        $category = $this->getCategory();
        if($category === null){
            return parent::configureCrud($crud);
        }
        // handle nestedset
        return parent::configureCrud($crud)
            ->setEntityLabelInSingular($category->getName());
    }
}
