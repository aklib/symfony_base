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
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractAppGrudController extends AbstractCrudController
{
    private EntityManagerInterface $em;
    private TranslatorInterface $translator;
    private array $mappings;


    public function __construct(EntityManagerInterface $em, TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->translator = $translator;
        $classMetadata = $this->getEntityManager()->getClassMetadata(static::getEntityFqcn());
        $mappings = array_replace_recursive($classMetadata->fieldMappings, $classMetadata->associationMappings);
        $count = 1;
        foreach ($mappings as &$mapping) {
            if (!array_key_exists('element', $mapping)) {
                $mapping['element']['sortOrder'] = $count;
            } elseif (!array_key_exists('sortOrder', $mapping['element'])) {
                $mapping['element']['sortOrder'] = $count;
            } else {
                $count = $mapping['element']['sortOrder'];
            }
            $count++;
        }
        unset($mapping);
        uasort($mappings, static function ($a, $b) {
            return $a['element']['sortOrder'] > $b['element']['sortOrder'];
        });
        $this->mappings = $mappings;
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
        foreach ($this->mappings as $propertyName => $mapping) {
            if (!$this->isVisibleProperty($propertyName)) {
                continue;
            }

            $label = ucfirst($propertyName);
            switch ($mapping['type']) {
                case 'integer':
                    if ($propertyName !== 'id') {
                        $fields[$propertyName] = IntegerField::new($propertyName, $label);
                    } else {
                        $fields[$propertyName] = IdField::new($propertyName, $label)->hideOnForm();
                    }
                    break;
                case 'float':
                case 'decimal':
                    $fields[$propertyName] = NumberField::new($propertyName, $label);
                    break;
                case 'boolean':
                    $fields[$propertyName] = BooleanField::new($propertyName, $label);
                    break;
                case 'datetime':
                case 'date_immutable':
                    $fields[$propertyName] = DateTimeField::new($propertyName, $label)->setFormat('y-MM-d hh:mm:ss');
                    if ('createdAt' === $propertyName || 'updatedAt' === $propertyName) {
                        $fields[$propertyName]->hideOnForm();
                    }
                    break;
                case 'date':
                    $fields[$propertyName] = DateField::new($propertyName, $label)->setFormat('y-MM-d hh:mm:ss')->hideOnForm();
                    break;
                case 'json':
                case 'array':
                case 'array_simple':
                    $fields[$propertyName] = ArrayField::new($propertyName, $label);
                    break;
                case ClassMetadataInfo::MANY_TO_MANY:
                case ClassMetadataInfo::ONE_TO_MANY:
                case ClassMetadataInfo::MANY_TO_ONE:
                    $fields[$propertyName] = AssociationField::new($propertyName, $label);
                    if ('createdBy' === $propertyName || 'updatedBy' === $propertyName) {
                        $fields[$propertyName]->hideOnForm();
                    } elseif ($mapping['type'] !== ClassMetadataInfo::MANY_TO_ONE) {
                        $fields[$propertyName]->formatValue(function ($v, $entity) use ($pageName, $mapping) {
                            if ($pageName === 'detail') {
                                $method = 'get' . ucfirst($mapping['fieldName']);
                                $collection = $entity->$method();
                                $result = [];
                                foreach ($collection as $item) {
                                    $result[] = (string)$item;
                                }
                                return implode('<br>', $result);
                            }
                            return $v;
                        });
                    }
                    break;
                default:
                    if ($propertyName === 'password') {
                        continue 2;
                    }

                    if ($propertyName === 'email') {
                        $fields[$propertyName] = EmailField::new($propertyName, $label);
                    } else {
                        $length = (int)$mapping['length'];
                        if ($length > 255) {
                            $fields[$propertyName] = TextareaField::new($propertyName, $label);
                        } elseif ($length > 0) {
                            $fields[$propertyName] = TextField::new($propertyName, $label);
                            $fields[$propertyName]->setHelp('field.max.length')->setTranslationParameters(['%count%' => $mapping['length']]);
                        } else {
                            $fields[$propertyName] = TextField::new($propertyName, $label);
                        }
                    }
                    $fields[$propertyName]->setCustomOption('sortOrder', $mapping['element']['sortOrder']);
            }
        }
        return $fields;
    }

    public function configureFilters(Filters $filters): Filters
    {
        foreach ($this->mappings as $propertyName => $mapping) {
            $filters->add($propertyName);
        }
        return $filters;
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
            })
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->addCssClass('btn btn-icon btn-sm btn-default')->setIcon('fa fa-eye')->setLabel(false)->setHtmlAttributes(['title' => 'Details']);
            })
            ->reorder(Crud::PAGE_INDEX, [Action::EDIT, Action::DETAIL, Action::DELETE]);
        return $actions;
    }

    /**
     * @return TranslatorInterface
     */
    public function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->em;
    }

    protected function isVisibleProperty(string $propertyName): bool
    {
        return true;
    }
}