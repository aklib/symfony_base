<?php

namespace App\Controller\Admin\Product;

use App\Bundles\Attribute\Constant;
use App\Controller\Admin\AbstractAppGrudController;
use App\Entity\Extension\Attributable\AttributeInterface;
use App\Entity\Product\ProductAttribute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

class ProductAttributeCrudController extends AbstractAppGrudController
{
    public static function getEntityFqcn(): string
    {
        return ProductAttribute::class;
    }

    public function getFieldOptions(string $pageName = 'index'): array
    {
        $fields = parent::getFieldOptions($pageName);
        $entity = $this->getEntity();
        if ($entity instanceof AttributeInterface) {
            if ($pageName === Crud::PAGE_NEW || $entity->getAttributeDef()->getType() !== 'select') {
                $fields['optionsArray'][Constant::OPTION_VISIBLE] = false;
            }
        } else {
            $fields['optionsArray'][Constant::OPTION_VISIBLE] = false;
        }
        if ($pageName === 'edit') {
            $attribute = $this->getEntity();
            if ($attribute instanceof AttributeInterface && !$attribute->getAttributeDef()->isCanMultiple()) {
                $fields['multiple'][Constant::OPTION_VISIBLE] = false;
            }
        } else {
            $fields['multiple'][Constant::OPTION_VISIBLE] = false;
        }
        return $fields;
    }
}
