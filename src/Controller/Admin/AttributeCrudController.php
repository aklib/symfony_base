<?php

namespace App\Controller\Admin;

use App\Entity\Attribute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class AttributeCrudController extends AbstractAppGrudController
{
    public static function getEntityFqcn(): string
    {
        return Attribute::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = parent::configureFields($pageName);
        $attributeOptionsField = $fields['attributeOptions'] ?? null;
        if ($attributeOptionsField instanceof AssociationField) {
            $attributeOptionsField->hideOnIndex();
        }
        return $fields;
    }

    protected function isVisibleProperty(string $propertyName, string $pageName = null): bool
    {

        $entity = $this->getEntity();
        if ($entity instanceof Attribute && $propertyName === 'attributeOptions') {
            if ($pageName === Crud::PAGE_NEW) {
                return false;
            }
            return $entity->getAttributeDefinition()->getType() === 'select';
        }
        return parent::isVisibleProperty($propertyName, $pageName);
    }

}
