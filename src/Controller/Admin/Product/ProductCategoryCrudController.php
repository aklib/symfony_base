<?php

namespace App\Controller\Admin\Product;

use App\Controller\Admin\AbstractAppGrudController;
use App\Entity\Product\ProductCategory;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;

class ProductCategoryCrudController extends AbstractAppGrudController
{
    public static function getEntityFqcn(): string
    {
        return ProductCategory::class;
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
        $fields = (array)parent::configureFields($pageName);
        if ($pageName === 'index') {
            /** @var FieldTrait $field */
            foreach ($fields as $field) {
                $field->setSortable(false);
            }
        }
        if (array_key_exists('attributes', $fields) && $fields['attributes'] instanceof AssociationField) {
            $fields['attributes']->setTemplatePath('bundles/EasyAdminBundle/crud/field/attribute_list_accordion.html.twig')->hideOnForm();
        }
        return $fields;
    }

    public function configureAssets(Assets $assets): Assets
    {
        return $assets
            ->addCssFile('/js/treegrid/jquery.treegrid.css')
            ->addJsFile('/js/jquery-3.1.1.min.js')
            ->addJsFile('/js/treegrid/jquery.treegrid.js');
    }

    public function excludeFields(string $pageName = null): array
    {
        $fields = array_merge(parent::excludeFields($pageName), ['lft', 'rgt', 'level', 'root', 'children']);
        if ($pageName !== 'index') {
            $fields[] = 'products';
        }
        return $fields;
    }
}
