<?php
/**
 * Class AbstractAttributableEntityController
 * @package App\Controller\Admin
 *
 * since: 13.06.2022
 * author: alexej@kisselev.de
 */

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Extension\AttributableEntity;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;

abstract class AbstractAttributableEntityController extends AbstractAppGrudController
{
    protected ?Category $category = null;

    /**
     * Set entity name like category
     * @param Crud $crud
     * @return Crud
     */
    public function configureCrud(Crud $crud): Crud
    {
        $category = $this->getCategory();
        if ($category !== null) {
            return parent::configureCrud($crud)->setEntityLabelInSingular($category->getName());
        }
        return parent::configureCrud($crud);
    }

    /**
     * Called only by index action. Filter a listing by category
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
        if ($category !== null) {
            $expr = $qb->expr()->between('category.lft', $category->getLft(), $category->getRgt());
            $qb->innerJoin('entity.category', 'category')->andWhere($expr);
            $crudDto = $this->getContext() !== null ? $this->getContext()->getCrud() : null;
            if ($crudDto !== null) {
                $crudDto->setCustomPageTitle(Crud::PAGE_INDEX, $category->getName());
            }
        }
        return $qb;
    }


    /**
     * Finds a category set in a dashboard controller
     * @return Category|null
     */
    public function getCategory(): ?Category
    {
        if ($this->category === null) {
            parse_str($_SERVER['QUERY_STRING'], $params);
            if (array_key_exists('category', $params)) {
                // index
                $categoryId = (int)$params['category'];
                $this->category = $this->getEntityManager()->getRepository(Category::class)->find($categoryId);
            } elseif (array_key_exists('entityId', $params)) {
                // edit
                $entityId = (int)$params['entityId'];
                $entity = $this->getEntityManager()->getRepository($this->getEntityFqcn())->find($entityId);
                if ($entity instanceof AttributableEntity) {
                    $category = $entity->getCategory();
                    if ($category === null) {
                        return null;
                    }
                    do {
                        if ($category->getLevel() > 1) {
                            $category = $category->getParent();
                        }
                    } while ($category->getLevel() > 1);
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