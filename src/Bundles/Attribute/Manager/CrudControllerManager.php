<?php /** @noinspection DuplicatedCode */

/**
 * Class EntityFieldsManager
 * @package App\Service
 *
 * since: 18.06.2022
 * author: alexej@kisselev.de
 */

namespace App\Bundles\Attribute\Manager;

use App\Bundles\Attribute\Constant;
use App\Bundles\Attribute\Form\AddressFormEmbedType;
use App\Controller\Admin\Extension\CrudControllerManagerInterface;
use App\Entity\Extension\Attributable\AttributeInterface;
use App\Entity\Extension\Attributable\CategoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Controller\CrudControllerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CountryField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CrudControllerManager implements EventSubscriberInterface
{
    private EntityManagerInterface $em;
    private ContainerBagInterface $parameterBug;
    protected string $entityFqcn;
    protected CrudControllerManagerInterface $controller;
    protected string $pageName;

    public function __construct(EntityManagerInterface $em, ContainerBagInterface $parameterBug)
    {
        $this->em = $em;
        $this->parameterBug = $parameterBug;
    }

    /**
     * @param array $fieldOptions [name => sortOrder<int>, visible<bool>]
     * @return iterable
     */
    public function configureFields(array $fieldOptions = []): iterable
    {
        $controller = $this->getController();
        $pageName = $this->getPageName();
        $mappings = $this->getMappings($controller);
//        dump($mappings);
        $fields = [];
        foreach ($mappings as $propertyName => &$mapping) {
            if (array_key_exists($propertyName, $fieldOptions)) {
                $visible = $fieldOptions[$propertyName][Constant::OPTION_VISIBLE] ?? true;

                if (!$visible) {
                    continue;
                }
            }
            if (array_key_exists($propertyName, $fieldOptions) && array_key_exists(Constant::OPTION_SORT_ORDER, $fieldOptions[$propertyName])) {
                $mapping['element'][Constant::OPTION_SORT_ORDER] = (int)$fieldOptions[$propertyName][Constant::OPTION_SORT_ORDER];
            }
            $field = $this->createField($mapping, $pageName, $controller);
            if ($field === null) {
                continue;
            }
            if (!array_key_exists('element', $mapping)) {
                $mapping['element'] = [];
            }
            if (!array_key_exists(Constant::OPTION_TAB, $mapping['element'])) {
                $mapping['element'][Constant::OPTION_TAB] = 'General';
            }
            $fields[$propertyName] = $field;
        }
        unset($mapping);
        if ($controller instanceof CrudControllerAttributableEntity) {
            foreach ($controller->getCategory()->getAttributes(true) as $attribute) {
                if (array_key_exists($attribute->getUniqueKey(), $fieldOptions)) {
                    $visible = $fieldOptions[$attribute->getUniqueKey()][Constant::OPTION_VISIBLE] ?? true;
                    if (!$visible) {
                        continue;
                    }
                }
                $mapping = $attribute->toMapping();

                $field = $this->createField($mapping, $pageName, $controller, $attribute);
                if ($field === null) {
                    continue;
                }
                $fields[$attribute->getUniqueKey()] = $field;
                $mappings[$attribute->getUniqueKey()] = $mapping;
            }
        }

        uasort($fields, static function ($a, $b) {
            return $a->getAsDto()->getCustomOption(Constant::OPTION_SORT_ORDER) > $b->getAsDto()->getCustomOption(Constant::OPTION_SORT_ORDER);
        });
        $doTab = $controller instanceof CrudControllerAttributableEntity && ($pageName === Crud::PAGE_DETAIL || $pageName === Crud::PAGE_EDIT || $pageName === Crud::PAGE_NEW);
        if ($doTab) {
            $byTab = [];
            foreach ($fields as $propertyName => $field) {
                if (!array_key_exists($propertyName, $mappings)) {
                    // can't be
                    continue;
                }
                $mapping = $mappings[$propertyName];
                $byTab[$mapping['element'][Constant::OPTION_TAB]][$propertyName] = $field;
            }
            $tabbedFields = [];
            foreach ($byTab as $tab => $fieldsOrdered) {
                $tabbedFields[$tab] = FormField::addPanel($tab);
                foreach ($fieldsOrdered as $propertyName => $field) {
                    $tabbedFields[$propertyName] = $field;
                }
            }
            $fields = $tabbedFields;
        }
        return $fields;
    }

    protected function createField(array $mapping, string $pageName, CrudControllerManagerInterface $controller, AttributeInterface $attribute = null): ?FieldInterface
    {
        $propertyName = $mapping['fieldName'];
        $label = $attribute === null ? ucfirst($propertyName) : $attribute->getName();
        $type = $mapping['type'];
        if (!empty($mapping['element']['type'])) {
            // overridden from annotation e.g. email
            $type = $mapping['element']['type'];
        }
        switch ($type) {
            case 'integer':
                if ($propertyName !== 'id') {
                    $field = IntegerField::new($propertyName, $label);
                } else {
                    $field = IdField::new($propertyName, $label)->hideOnForm();
                }
                break;
            case 'float':
            case 'decimal':
                $field = NumberField::new($propertyName, $label);
                break;
            case 'price':
                $field = MoneyField::new($propertyName, $label)->setCurrency('EUR');
                break;
            case 'boolean':
                $field = BooleanField::new($propertyName, $label);
                break;
            case 'datetime':
            case 'date_immutable':
                $field = DateTimeField::new($propertyName, $label)->setFormat('y-MM-dd hh:mm:ss');
                if ('createdAt' === $propertyName || 'updatedAt' === $propertyName) {
                    $field->hideOnForm();
                }
                break;
            case 'date':
                $field = DateField::new($propertyName, $label)->setFormat('y-MM-dd');
                if ('createdAt' === $propertyName || 'updatedAt' === $propertyName) {
                    $field->hideOnForm();
                }
                break;
            case 'json':
            case 'array':
            case 'array_simple':
                $field = ArrayField::new($propertyName, $label);
                break;
            case ClassMetadataInfo::MANY_TO_MANY:
            case ClassMetadataInfo::ONE_TO_MANY:
            case ClassMetadataInfo::MANY_TO_ONE:
            case ClassMetadataInfo::ONE_TO_ONE:
                $field = AssociationField::new($propertyName, $label);
                if ('createdBy' === $propertyName || 'updatedBy' === $propertyName) {
                    // user
                    $field->hideOnForm();
                }
                if ($mapping['type'] !== ClassMetadataInfo::MANY_TO_ONE) {
                    $field->formatValue(function ($v, $entity) use ($pageName, $mapping) {
                        if ($pageName === 'detail') {
                            $method = 'get' . ucfirst($mapping['fieldName']);
                            $collection = $entity->$method();
                            if (is_iterable($collection)) {
                                $result = [];
                                foreach ($collection as $item) {
                                    $result[] = (string)$item;
                                }
                                return implode('<br>', $result);
                            }
                            return $collection;
                        }
                        return $v;
                    });
                }

                if ($propertyName === 'category' && $controller instanceof CrudControllerAttributableEntity) {
                    /** @var CategoryInterface $category */
                    $category = $controller->getCategory();

                    $field->setQueryBuilder(
                        fn(QueryBuilder $qb) => $qb
                            ->andWhere($qb->expr()->between('entity.lft', $category->getLft(), $category->getRgt()))
                            ->orderBy('entity.lft')

                    );
                }
                break;

            // attributes and custom annotations
            case 'email':
                $field = EmailField::new($propertyName, $label);
                break;
            case 'text':
                $field = TextareaField::new($propertyName, $label);
                break;
            case 'string':
                $field = TextField::new($propertyName, $label);
                if ($propertyName === 'uniqueKey') {
                    $field->setHelp('')->hideOnForm();
                }
                break;
            case 'password':
                return null;
            case 'html':
                $field = TextEditorField::new($propertyName, $label);
                break;
            case 'country':
                $field = CountryField::new($propertyName, $label);
                if ($attribute->isMultiple()) {
                    $field->setFormTypeOption('multiple', true);
                }
                break;
            case 'url':
                $field = UrlField::new($propertyName, $label);
                break;
            case 'select':
                $field = ChoiceField::new($propertyName, $label);
                $choices = [];
                foreach ($attribute->getOptionsArray() as $attributeOption) {
                    $choices[$attributeOption] = $attributeOption;
                }
                $field
                    ->setChoices($choices)
                    ->allowMultipleChoices($attribute->isMultiple());
                break;
            case 'options':
                if ($attribute instanceof AttributeInterface) {
                    $field = ChoiceField::new($propertyName, $label);
                    $choices = [];
                    foreach ($attribute->getOptionsArray() as $attributeOption) {
                        $choices[$attributeOption] = $attributeOption;
                    }
                    $field
                        ->setChoices($choices)
                        ->allowMultipleChoices($attribute->isMultiple());
                } else {
                    $field = ArrayField::new($propertyName, $label);
                }
                break;
            case 'image':
                $imagePath = $this->getParameter('upload_image_path');
                $field = ImageField::new($propertyName, $label)
                    ->setUploadDir("public/$imagePath")
                    ->setBasePath($imagePath);//->setFormTypeOption('multiple', true);

//                $field = CollectionField::new($propertyName, $label)
//                    ->setEntryType(AttributeFileType::class)
//                    ->setFormTypeOption('by_reference', false)
//                ;
//                if ($pageName === Crud::PAGE_DETAIL) {
//                    $field->setTemplatePath('bundles/EasyAdminBundle/crud/field/attribute_images.html.twig');
//                }

                break;
            case 'address':
                $field = CollectionField::new($propertyName, $label)
                    ->setEntryType(AddressFormEmbedType::class)
                    ->setTemplatePath('bundles/EasyAdminBundle/crud/field/attribute_address.html.twig');

                break;
            case 'birthday':
                $field = DateField::new($propertyName, $label)->setFormat('y-MM-dd');
                $field->setFormType(BirthdayType::class);
                break;
            default:
                $field = TextField::new($propertyName, $label);
        }

        if (empty($mapping['element']['help'])) {
            $length = (int)($mapping['length'] ?? 0);
            if ($length > 0 && $field->getAsDto()->getHelp() === null) {
                $field->setHelp('field.max.length')->setTranslationParameters(['%count%' => $mapping['length']]);
            }
        } else {
            $field->setHelp($mapping['element']['help']);
        }

        if ($attribute === null) {
            $field->setCustomOption(Constant::OPTION_SORT_ORDER, $mapping['element'][Constant::OPTION_SORT_ORDER]);
        } else {
            $field->setCustomOption(Constant::OPTION_SORT_ORDER, $attribute->getSortOrder());
        }
        return $field;
    }

    public function getMappings(CrudControllerInterface $controller): array
    {
        $classMetadata = $this->getEntityManager()->getClassMetadata($controller::getEntityFqcn());

        $mappings = array_replace_recursive($classMetadata->fieldMappings, $classMetadata->associationMappings);
        $count = 1;
        foreach ($mappings as &$mapping) {
            if (!array_key_exists('element', $mapping)) {
                $mapping['element'][Constant::OPTION_SORT_ORDER] = $count;
            } elseif (!array_key_exists(Constant::OPTION_SORT_ORDER, $mapping['element'])) {
                $mapping['element'][Constant::OPTION_SORT_ORDER] = $count;
            } else {
                $count = $mapping['element'][Constant::OPTION_SORT_ORDER];
            }
            $count++;
        }
        unset($mapping);
        uasort($mappings, static function ($a, $b) {
            return $a['element'][Constant::OPTION_SORT_ORDER] > $b['element'][Constant::OPTION_SORT_ORDER];
        });
        return $mappings;
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->em;
    }

    protected function getParameter(string $name)
    {
        try {
            return $this->parameterBug->get($name);
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            error_log($e);
        }
        return null;
    }

    //==================== EVENT ====================
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $controller = $event->getController();
        if (is_array($controller)) {
            if ($controller[0] instanceof CrudControllerManagerInterface) {
                $this->setController($controller[0]);
            }
            if (is_string($controller[1])) {
                $this->setPageName($controller[1]);
            }
        }
    }

    /**
     * @return CrudControllerManagerInterface
     */
    public function getController(): CrudControllerManagerInterface
    {
        return $this->controller;
    }

    /**
     * @param CrudControllerManagerInterface $controller
     * @return CrudControllerManager
     */
    public function setController(CrudControllerManagerInterface $controller): CrudControllerManager
    {
        $this->controller = $controller;
        return $this;
    }

    /**
     * @return string
     */
    public function getPageName(): string
    {
        return $this->pageName;
    }

    /**
     * @param string $pageName
     * @return CrudControllerManager
     */
    public function setPageName(string $pageName): CrudControllerManager
    {
        $this->pageName = $pageName;
        return $this;
    }


}