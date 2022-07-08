<?php

namespace App\Controller\Admin\Product;

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

    public function excludeFields(string $pageName = 'index'): array
    {
        $fields = parent::excludeFields($pageName);
        $entity = $this->getEntity();
        if ($entity instanceof AttributeInterface) {
            if ($pageName === Crud::PAGE_NEW || $entity->getAttributeDef()->getType() !== 'select') {
                $fields[] = 'attributeOptions';
            }
        } else {
            $fields[] = 'attributeOptions';
        }
        if ($pageName === 'edit') {
            $attribute = $this->getEntity();
            if ($attribute instanceof AttributeInterface && !$attribute->getAttributeDef()->isCanMultiple()) {
                $fields[] = 'multiple';
            }
        } else {
            $fields[] = 'multiple';
        }
        return $fields;
    }
}