<?php

namespace App\Controller\Admin;

use App\Entity\Attribute;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class AttributeCrudController extends AbstractAppGrudController
{
    public static function getEntityFqcn(): string
    {
        return Attribute::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
