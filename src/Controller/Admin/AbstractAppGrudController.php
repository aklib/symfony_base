<?php
/**
 * Class AbstractAppGrudController
 * @package App\Controller\Admin
 *
 * since: 25.05.2022
 * author: alexej@kisselev.de
 */

namespace App\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

abstract class AbstractAppGrudController extends AbstractCrudController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Customise action icons view in a table
     * @param Crud $crud
     * @return Crud
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud->showEntityActionsInlined();
    }

    /**
     * @param string $pageName
     * @return iterable
     */
    public function configureFields(string $pageName): iterable
    {
        $fields = [];
        $classMetadata = $this->getEntityManager()->getClassMetadata(static::getEntityFqcn());
        $mappings = array_replace_recursive($classMetadata->fieldMappings, $classMetadata->associationMappings);
        foreach ($mappings as $propertyName => $mapping) {
            switch ($mapping['type']) {
                case 'integer':
                    if ($propertyName !== 'id') {
                        $fields[$propertyName] = IntegerField::new($propertyName, ucfirst($propertyName));
                    } else {
                        $fields[$propertyName] = IdField::new($propertyName, ucfirst($propertyName))->hideOnForm();
                    }
                    break;
                case 'float':
                case 'decimal':
                    $fields[$propertyName] = NumberField::new($propertyName, ucfirst($propertyName));
                    break;
                case 'boolean':
                    $fields[$propertyName] = BooleanField::new($propertyName, ucfirst($propertyName));
                    break;
                case 'datetime':
                case 'date_immutable':
                    $fields[$propertyName] = DateTimeField::new($propertyName, ucfirst($propertyName))->setFormat('y-MM-d hh:mm:ss')->hideOnForm();
                    break;
                case 'date':
                    $fields[$propertyName] = DateField::new($propertyName, ucfirst($propertyName))->setFormat('y-MM-d hh:mm:ss')->hideOnForm();
                    break;
                case 'json':
                case 'array':
                case 'array_simple':
                    $fields[$propertyName] = ArrayField::new($propertyName, ucfirst($propertyName));
                    break;
                case ClassMetadataInfo::MANY_TO_MANY:
                case ClassMetadataInfo::ONE_TO_MANY:
                case ClassMetadataInfo::MANY_TO_ONE:
                    $fields[$propertyName] = AssociationField::new($propertyName, ucfirst($propertyName));
                    break;
                default:
                    if ($propertyName === 'password') {
                        continue 2;
                    }
                    if ($propertyName === 'email') {
                        $fields[$propertyName] = EmailField::new($propertyName, ucfirst($propertyName));
                    } else {
                        $fields[$propertyName] = TextField::new($propertyName, ucfirst($propertyName));
                    }
            }
        }
        return $this->postConfigureFields($fields);
    }


    /**
     * Customise action icons
     * @param Actions $actions
     * @return Actions
     */
    public function configureActions(Actions $actions): Actions
    {
        $actions->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
            return $action->addCssClass('btn btn-icon btn-sm btn-default')->setIcon('fa fa-pencil')->setLabel(false)->setHtmlAttributes(['title' => 'Edit']);
        })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->addCssClass('btn btn-icon btn-sm btn-default')->setIcon('fa fa-trash')->setLabel(false)->setHtmlAttributes(['title' => 'Delete']);
            });
        return $actions;
    }


    protected function postConfigureFields(array $fields): iterable
    {
        return $fields;
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->em;
    }
}