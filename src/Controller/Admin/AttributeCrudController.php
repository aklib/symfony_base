<?php

namespace App\Controller\Admin;

use App\Entity\Attribute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

class AttributeCrudController extends AbstractAppGrudController
{
    public static function getEntityFqcn(): string
    {
        return Attribute::class;
    }

    public function excludeFields(string $pageName = 'index'): array
    {
        $fields = parent::excludeFields($pageName);
        $entity = $this->getEntity();
        if ($entity instanceof Attribute) {
            if ($pageName === Crud::PAGE_NEW || $entity->getAttributeDefinition()->getType() !== 'select') {
                $fields[] = 'attributeOptions';
            }
        } else {
            $fields[] = 'attributeOptions';
        }
        if ($pageName === 'edit') {
            $attribute = $this->getEntity();
            if ($attribute instanceof Attribute && !$attribute->getAttributeDefinition()->isCanMultiple()) {
                $fields[] = 'multiple';
            }
        } else {
            $fields[] = 'multiple';
        }
        return $fields;
    }
}
