<?php

namespace App\Controller\Admin\Campaign;

use App\Bundles\Attribute\Constant;
use App\Controller\Admin\AbstractCategoryCrudController;
use App\Entity\Campaign\CampaignCategory;

class CampaignCategoryCrudController extends AbstractCategoryCrudController
{
    public static function getEntityFqcn(): string
    {
        return CampaignCategory::class;
    }

    public function getFieldOptions(string $pageName = null): array
    {
        $fields = parent::getFieldOptions($pageName);
        if ($pageName !== 'index') {
            $fields['campaigns'][Constant::OPTION_VISIBLE] = false;
        }
        return $fields;
    }
}
