<?php
/**
 * Class AbstractAttributableEntityController
 * @package App\Controller\Admin
 *
 * since: 13.06.2022
 * author: alexej@kisselev.de
 */

namespace App\Controller\Admin;

use App\Bundles\Attribute\Controller\CrudControllerAttributableEntity;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use Elastica\Util;
use InvalidArgumentException;

abstract class AbstractAttributableEntityCrudController extends AbstractAppGrudController implements CrudControllerAttributableEntity
{
    protected ?Category $category = null;

    /**
     * Set entity name like category
     * @param Crud $crud
     * @return Crud
     */
//    public function configureCrud(Crud $crud): Crud
//    {
//        //$crud->setFormThemes(['bundles/EasyAdminBundle/crud/attribute_form_theme.html.twig', '@EasyAdmin/crud/form_theme.html.twig']);
//        $category = $this->getCategory();
//        if ($category !== null) {
//            return parent::configureCrud($crud)->setEntityLabelInSingular($category->getName());
//        }
//
//        return parent::configureCrud($crud);
//    }

    /**
     * Called only by index action. Filter a listing by category
     * @param SearchDto $searchDto
     * @param EntityDto $entityDto
     * @param FieldCollection $fields
     * @param FilterCollection $filters
     * @return QueryBuilder
     */
//    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
//    {
//        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
//        $category = $this->getCategory();
//
//            $expr = $qb->expr()->between('cat.lft', $category->getLft(), $category->getRgt());
//            $qb->innerJoin('entity.category', 'cat')->andWhere($expr);
//            $crudDto = $this->getContext() !== null ? $this->getContext()->getCrud() : null;
//            if ($crudDto !== null) {
//                $crudDto->setCustomPageTitle(Crud::PAGE_INDEX, $category->getName());
//            }
//
//        return $qb;
//    }


    /**
     * Finds a category set in a dashboard controller
     * @return Category|null
     */
    public function getCategory(): Category
    {
        if ($this->category === null) {
            $uniqueKey = $this->getScope($this->getEntityFqcn());
            /** @var CategoryRepository $dao */
            $dao = $this->getEntityManager()->getRepository(Category::class);
            $this->category = $dao->findOneByUniqueKey($uniqueKey);
            if ($this->category === null) {
                throw new InvalidArgumentException("The category with unique key '$uniqueKey' not exists. Please first create one.");
            }
        }
        return $this->category;
    }

    /**
     * Gets entity class name in a snake case e.g. App\Entity\UserProfile to user_profile
     * @param string $entityFqcn
     * @return string
     */
    private function getScope(string $entityFqcn): string
    {
        return Util::toSnakeCase(substr(strrchr($entityFqcn, '\\'), 1));
    }
}