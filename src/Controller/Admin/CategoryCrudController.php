<?php

namespace App\Controller\Admin;

use App\Entity\Category;

class CategoryCrudController extends AbstractAppGrudController
{
    public static function getEntityFqcn(): string
    {
        return Category::class;
    }

    protected function isVisibleProperty(string $propertyName): bool
    {
        switch ($propertyName){
            case 'lft':
            case 'rgt':
            case 'level':
            case 'root':
            case 'children':
            case 'products':
                return false;
        }
        return parent::isVisibleProperty($propertyName);
    }
}
