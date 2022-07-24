<?php

namespace App\Controller\Admin\Campaign;

use App\Entity\Campaign\CampaignAttributeTab;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CampaignAttributeTabCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CampaignAttributeTab::class;
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
