<?php

namespace App\Controller\Admin\Campaign;

use App\Entity\Campaign\CampaignAttribute;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CampaignAttributeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CampaignAttribute::class;
    }
}
