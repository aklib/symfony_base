<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ProductCrudController extends AbstractAppGrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    /**
     * @param string $pageName
     * @return iterable
     */
    public function configureFields(string $pageName): iterable
    {
        parent::configureFields($pageName);
        return [
            TextField::new('name', 'name'),
            AssociationField::new('category', 'category')
        ];
    }

}
