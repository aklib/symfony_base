<?php

namespace App\Controller\Admin\Campaign;

use App\Controller\Admin\AbstractAttributableEntityController;
use App\Entity\Campaign\Campaign;

class CampaignCrudController extends AbstractAttributableEntityController
{
    public static function getEntityFqcn(): string
    {
        return Campaign::class;
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
