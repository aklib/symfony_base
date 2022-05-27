<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
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
        return [
            TextField::new('name', 'Name'),
            AssociationField::new('category', 'Category'),
            DateTimeField::new('createdAt', 'Created')->setFormat('y-MM-d hh:mm:ss')->hideOnForm(),
            DateTimeField::new('updatedAt', 'Updated')->setFormat('y-MM-d hh:mm:ss')->hideOnForm()
        ];
    }
}
