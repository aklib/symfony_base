<?php

namespace App\Controller\Admin;

use App\Entity\AttributeType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class AttributeTypeCrudController extends AbstractAppGrudController
{
    public static function getEntityFqcn(): string
    {
        return AttributeType::class;
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
