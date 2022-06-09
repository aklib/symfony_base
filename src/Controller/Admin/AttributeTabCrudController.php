<?php

namespace App\Controller\Admin;

use App\Entity\AttributeTab;

class AttributeTabCrudController extends AbstractAppGrudController
{
    public static function getEntityFqcn(): string
    {
        return AttributeTab::class;
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
