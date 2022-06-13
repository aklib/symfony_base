<?php

namespace App\Controller\Admin;

use App\Entity\AttributeDefinition;

class AttributeTypeCrudController extends AbstractAppGrudController
{
    public static function getEntityFqcn(): string
    {
        return AttributeDefinition::class;
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
