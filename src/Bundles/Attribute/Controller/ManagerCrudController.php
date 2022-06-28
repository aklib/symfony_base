<?php /** @noinspection DuplicatedCode */

/**
 * Class EntityFieldsManager
 * @package App\Service
 *
 * since: 18.06.2022
 * author: alexej@kisselev.de
 */

namespace App\Bundles\Attribute\Controller;

use App\Bundles\Attribute\Form\AddressFormEmbed;
use App\Controller\Admin\CrudControllerManagerInterface;
use App\Entity\Attribute;
use App\Entity\AttributeOption;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
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
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;

class ManagerCrudController
{
    private EntityManagerInterface $em;
    private ContainerBagInterface $parameterBug;
    protected const OPTION_SORT_ORDER = 'sortOrder';
    protected string $entityFqcn;

    public function __construct(EntityManagerInterface $em, ContainerBagInterface $parameterBug)
    {
        $this->em = $em;
        $this->parameterBug = $parameterBug;
    }

    public function configureFields(CrudControllerManagerInterface $controller, string $pageName, array $excludeFields = []): iterable
    {
        $mappings = $this->getMappings($controller);
        $fields = [];
        foreach ($mappings as $propertyName => $mapping) {
            if (in_array($propertyName, $excludeFields, true)) {
                continue;
            }
            $field = $this->createField($mapping, $pageName);
            if ($field === null) {
                continue;
            }
            $fields[$propertyName] = $field;
        }
        if ($controller instanceof CrudControllerAttributableEntity && $controller->getCategory() instanceof Category) {
            foreach ($controller->getCategory()->getAttributes(true) as $attribute) {
                $mapping = $attribute->toMapping();
                $field = $this->createField($mapping, $pageName, $attribute);
                if ($field === null) {
                    continue;
                }
                $fields[$attribute->getUniqueKey()] = $field;
            }
        }

        uasort($fields, static function ($a, $b) {
            return $a->getAsDto()->getCustomOption(self::OPTION_SORT_ORDER) > $b->getAsDto()->getCustomOption(self::OPTION_SORT_ORDER);
        });
        return $fields;
    }

    protected function createField(array $mapping, string $pageName, Attribute $attribute = null): ?FieldInterface
    {
        $propertyName = $mapping['fieldName'];
        $label = $attribute === null ? ucfirst($propertyName) : $attribute->getName();
        $type = $mapping['type'];
        if (!empty($mapping['element']['type'])) {
            // overridden from annotation e.g. email
            $type = $mapping['element']['type'];
        }
        $field = null;
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
                if ($attribute === null) {
                    $field = BooleanField::new($propertyName, $label);
                } else {
                    $fields[$propertyName] = ChoiceField::new($propertyName, $attribute->getName());
                    $fields[$propertyName]->setChoices(
                        [
                            'yes'   => true,
                            'no'    => false,
                            'maybe' => null
                        ]);
                }
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
                if ($propertyName === 'uniqueKey' && $pageName !== 'new') {
                    $field->setHelp('');
                    $field->setDisabled();
                }
                break;
            case 'password':
                return null;
            case 'html':
                $field = TextEditorField::new($propertyName, $attribute->getName());
                break;
            case 'country':
                $field = CountryField::new($propertyName, $attribute->getName());
                if ($attribute->isMultiple()) {
                    $field->setFormTypeOption('multiple', true);
                }
                break;
            case 'url':
                $field = UrlField::new($propertyName, $attribute->getName());
                break;
            case 'select':
                $field = ChoiceField::new($propertyName, $attribute->getName());
                $choices = [];
                /** @var AttributeOption $attributeOption */
                foreach ($attribute->getAttributeOptions() as $attributeOption) {
                    $choices[$attributeOption->getName()] = $attributeOption->getId();
                }
                $field->setChoices($choices);
                if (!$attribute->isMultiple()) {
                    $field->allowMultipleChoices(false);
                }
                break;
            case 'image':
                $folder = $attribute === null ? $propertyName : $attribute->getUniqueKey();
                $imagePath = $this->getParameter('upload_image_path') . '/' . $folder;
                $field = ImageField::new($propertyName, $attribute->getName())
                    ->setUploadDir("public/$imagePath")
                    ->setBasePath($imagePath);
                break;
            case 'address':
                $field = CollectionField::new($propertyName, $attribute->getName())
                    ->setEntryType(AddressFormEmbed::class)
                    ->setTemplatePath('bundles/EasyAdminBundle/crud/field/attribute_address.html.twig');

                break;
            case 'birthday':
                $field = DateField::new($propertyName, $label)->setFormat('y-MM-dd');
                $field->setFormType(BirthdayType::class);
                break;
            default:
                $field = TextField::new($propertyName, $label);
        }

        if ($field === null) {
            return null;
        }

        $length = (int)($mapping['length'] ?? 0);
        if ($length > 0 && $field->getAsDto()->getHelp() === null) {
            $field->setHelp('field.max.length')->setTranslationParameters(['%count%' => $mapping['length']]);
        }
        if ($attribute === null) {
            $field->setCustomOption(self::OPTION_SORT_ORDER, $mapping['element'][self::OPTION_SORT_ORDER]);
        } else {
            $field->setCustomOption(self::OPTION_SORT_ORDER, $attribute->getSortOrder());
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
                $mapping['element'][self::OPTION_SORT_ORDER] = $count;
            } elseif (!array_key_exists(self::OPTION_SORT_ORDER, $mapping['element'])) {
                $mapping['element'][self::OPTION_SORT_ORDER] = $count;
            } else {
                $count = $mapping['element'][self::OPTION_SORT_ORDER];
            }
            $count++;
        }
        unset($mapping);
        uasort($mappings, static function ($a, $b) {
            return $a['element'][self::OPTION_SORT_ORDER] > $b['element'][self::OPTION_SORT_ORDER];
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
}