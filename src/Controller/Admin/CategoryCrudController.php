<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;

class CategoryCrudController extends AbstractAppGrudController
{
    public static function getEntityFqcn(): string
    {
        return Category::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        // handle nestedset
        return parent::configureCrud($crud)
            ->setDefaultSort(['lft' => 'ASC'])->setPaginatorPageSize(1000)
            ->overrideTemplate('crud/index', 'bundles/EasyAdminBundle/crud/category/index.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = parent::configureFields($pageName);
        if ($pageName === 'index') {
            /** @var FieldTrait $field */
            foreach ($fields as $field) {
                $field->setSortable(false);
            }
        }


        return $fields;
    }

    public function configureAssets(Assets $assets): Assets
    {
        return $assets
            ->addCssFile('/js/treegrid/jquery.treegrid.css')
            ->addJsFile('https://code.jquery.com/jquery-3.1.1.min.js')
            ->addJsFile('/js/treegrid/jquery.treegrid.js');
    }



    protected function isVisibleProperty(string $propertyName): bool
    {
        switch ($propertyName) {
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
