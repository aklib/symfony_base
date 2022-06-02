<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;

class CategoryCrudController extends AbstractAppGrudController
{
    public static function getEntityFqcn(): string
    {
        return Category::class;
    }

    protected function isVisible(string $propertyName): bool
    {
        switch ($propertyName){
            case 'lft':
            case 'rgt':
            case 'level':
            case 'root':
                return false;
        }
        return parent::isVisible($propertyName);
    }

    /*public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),

        ];
    }*/

}
