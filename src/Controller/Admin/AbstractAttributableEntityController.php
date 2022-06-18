<?php
/**
 * Class AbstractAttributableEntityController
 * @package App\Controller\Admin
 *
 * since: 13.06.2022
 * author: alexej@kisselev.de
 */

namespace App\Controller\Admin;

use App\Entity\Attribute;
use App\Entity\AttributeOption;
use App\Entity\Category;
use App\Entity\Product;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CountryField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use InvalidArgumentException;

abstract class AbstractAttributableEntityController extends AbstractAppGrudController
{
    private ?Category $category = null;

    public function configureFields(string $pageName): iterable
    {
        $fields = parent::configureFields($pageName);
        if ($pageName === Crud::PAGE_NEW) {
            return $fields;
        }
        $category = $this->getCategory();
        if ($pageName !== Crud::PAGE_INDEX) {
            $entityDto = $this->getContext() !== null ? $this->getContext()->getEntity() : null;
            /** @var EntityDto $entityDto */
            if ($entityDto !== null && $entityDto->getInstance() !== null) {
                $category = $entityDto->getInstance()->getCategory();
            }
        }
        if ($category === null) {
            return $fields;
        }

        /** @var Attribute $attribute */
        foreach ($category->getAttributes(true) as $attribute) {
            switch ($attribute->getAttributeDefinition()->getType()) {
                case 'string':
                    $fields[$attribute->getUniqueKey()] = TextField::new($attribute->getUniqueKey(), $attribute->getName());
                    break;
                case 'text':
                    $fields[$attribute->getUniqueKey()] = TextareaField::new($attribute->getUniqueKey(), $attribute->getName());
                    break;
                case 'html':
                    $fields[$attribute->getUniqueKey()] = TextEditorField::new($attribute->getUniqueKey(), $attribute->getName());
                    break;
                case 'country':
                    $fields[$attribute->getUniqueKey()] = CountryField::new($attribute->getUniqueKey(), $attribute->getName());
                    if ($attribute->isMultiple()) {
                        $fields[$attribute->getUniqueKey()]->setFormTypeOption('multiple', true);
                    }
                    break;
                case 'integer':
                    $fields[$attribute->getUniqueKey()] = IntegerField::new($attribute->getUniqueKey(), $attribute->getName());
                    break;
                case 'float':
                case 'decimal':
                    $fields[$attribute->getUniqueKey()] = NumberField::new($attribute->getUniqueKey(), $attribute->getName());
                    break;
                case 'price':
                    $fields[$attribute->getUniqueKey()] = MoneyField::new($attribute->getUniqueKey(), $attribute->getName())->setCurrency('EUR');
                    break;
                case 'boolean':
                    $fields[$attribute->getUniqueKey()] = ChoiceField::new($attribute->getUniqueKey(), $attribute->getName());
                    $fields[$attribute->getUniqueKey()]->setChoices(
                        [
                            'yes'   => true,
                            'no'    => false,
                            'maybe' => null
                        ]);
                    break;
                case 'email':
                    $fields[$attribute->getUniqueKey()] = EmailField::new($attribute->getUniqueKey(), $attribute->getName());
                    break;
                case 'url':
                    $fields[$attribute->getUniqueKey()] = UrlField::new($attribute->getUniqueKey(), $attribute->getName());
                    break;
                case 'date':
                    $fields[$attribute->getUniqueKey()] = DateField::new($attribute->getUniqueKey(), $attribute->getName());
                    break;
                case 'datetime':
                    $fields[$attribute->getUniqueKey()] = DateTimeField::new($attribute->getUniqueKey(), $attribute->getName());
                    break;
                case 'select':
                    $fields[$attribute->getUniqueKey()] = ChoiceField::new($attribute->getUniqueKey(), $attribute->getName())->setRequired(true);
                    $choices = [];
                    /** @var AttributeOption $attributeOption */
                    foreach ($attribute->getAttributeOptions() as $attributeOption) {
                        $choices[$attributeOption->getName()] = $attributeOption->getId();
                    }
                    $fields[$attribute->getUniqueKey()]->setChoices($choices);
                    if (!$attribute->isMultiple()) {
                        $fields[$attribute->getUniqueKey()]->allowMultipleChoices(false);
                    }
                    break;
                default:
                    throw new InvalidArgumentException(sprintf('Unknown attribute type %s', $attribute->getAttributeDefinition()->getType()));
            }
            if ($attribute->isRequired()) {
                $fields[$attribute->getUniqueKey()]->setRequired(true);
            }
            $fields[$attribute->getUniqueKey()]->setCustomOption(self::OPTION_SORT_ORDER, $attribute->getSortOrder());
        }
        $this->postConfigureFields($fields, $pageName);
        return $fields;
    }

    public function postConfigureFields(iterable &$fields, string $pageName): void
    {
        if (is_array($fields)) {
            uasort($fields, static function ($a, $b) {
                return $a->getAsDto()->getCustomOption(self::OPTION_SORT_ORDER) > $b->getAsDto()->getCustomOption(self::OPTION_SORT_ORDER);
            });
        }
    }

    /**
     * Called only by index action
     * @param SearchDto $searchDto
     * @param EntityDto $entityDto
     * @param FieldCollection $fields
     * @param FilterCollection $filters
     * @return QueryBuilder
     */
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $category = $this->getCategory();
        if($category !== null){
            $expr = $qb->expr()->between('category.lft', $category->getLft(), $category->getRgt());
            $qb->innerJoin('entity.category', 'category')->andWhere($expr);
            $crudDto = $this->getContext() !== null ? $this->getContext()->getCrud() : null;
            if ($crudDto !== null) {
                $crudDto->setCustomPageTitle(Crud::PAGE_INDEX, $category->getName());
            }
        }
        return $qb;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $category = $this->getCategory();
        if($category !== null){
            return parent::configureCrud($crud)->setEntityLabelInSingular($category->getName());
        }
        return parent::configureCrud($crud);
    }

    /**
     * Finds a category set in a dashboard controller
     * @return Category|null
     */
    protected function getCategory(): ?Category
    {
        if ($this->category === null) {
            parse_str($_SERVER['QUERY_STRING'], $params);
            if (array_key_exists('category', $params)) {
                // index
                $categoryId = (int)$params['category'];
                $this->category = $this->getEntityManager()->getRepository(Category::class)->find($categoryId);
            }
            elseif (array_key_exists('entityId', $params)) {
                // edit
                $productId = (int)$params['entityId'];
                $product = $this->getEntityManager()->getRepository(Product::class)->find($productId);
                if ($product instanceof Product) {
                    $category = $product->getCategory();
                    if ($category === null) {
                        return null;
                    }
                    do {
                        if ($category->getParent() !== null) {
                            $category = $category->getParent();
                        }
                    }
                    while ($category->getParent() !== null);
                    $this->category = $category;
                }

            } else {
                //  new
                return null;
            }
        }
        return $this->category;
    }
}