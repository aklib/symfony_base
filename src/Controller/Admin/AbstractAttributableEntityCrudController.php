<?php
/**
 * Class AbstractAttributableEntityController
 * @package App\Controller\Admin
 *
 * since: 13.06.2022
 * author: alexej@kisselev.de
 */

namespace App\Controller\Admin;

use App\Bundles\Attribute\AttributeManagerInterface;
use App\Bundles\Attribute\Controller\CrudControllerAttributableEntity;
use App\Bundles\Attribute\Controller\CrudControllerManager;
use App\Bundles\Attribute\Entity\AttributableEntity;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use Elastica\Util;
use InvalidArgumentException;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractAttributableEntityCrudController extends AbstractAppGrudController implements CrudControllerAttributableEntity
{
    private ?Category $category = null;
    protected AttributeManagerInterface $attributeManager;

    public function __construct(EntityManagerInterface $em, TranslatorInterface $translator, CrudControllerManager $controllerManager, AttributeManagerInterface $attributeManager)
    {
        parent::__construct($em, $translator, $controllerManager);
        $this->attributeManager = $attributeManager;
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
        $this->attributeManager->search($qb, $searchDto, $entityDto, $fields);
        return $qb;
    }

    /**
     * Finds a category set in a dashboard controller
     * @return Category|null
     */
    public function getCategory(): Category
    {
        if ($this->category === null) {
            $entity = $this->getEntity();
            if ($entity instanceof AttributableEntity) {
                return $this->category = $entity->getCategory();
            }
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