<?php /** @noinspection PhpUnused */
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedPrivateMethodInspection */

/**
 * Class AbstractAttributableEntityController
 * @package App\Controller\Admin
 *
 * since: 13.06.2022
 * author: alexej@kisselev.de
 */

namespace App\Controller\Admin;

use App\Bundles\Attribute\AttributeAdapterInterface;
use App\Bundles\Attribute\Controller\CrudControllerAttributableEntity;
use App\Bundles\Attribute\Controller\CrudControllerManager;
use App\Entity\Extension\Attributable\AttributableEntity;
use App\Entity\Extension\Attributable\AttributeInterface;
use App\Entity\Extension\Attributable\CategoryInterface;
use App\Entity\Product\ProductCategory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterCrudActionEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeCrudActionEvent;
use EasyCorp\Bundle\EasyAdminBundle\Exception\ForbiddenActionException;
use EasyCorp\Bundle\EasyAdminBundle\Security\Permission;
use Elastica\Util;
use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractAttributableEntityController extends AbstractAppGrudController implements CrudControllerAttributableEntity
{
    private ?CategoryInterface $category = null;
    protected AttributeAdapterInterface $attributeManager;

    public function __construct(EntityManagerInterface $em, CrudControllerManager $controllerManager, AttributeAdapterInterface $attributeManager)
    {
        parent::__construct($em, $controllerManager);
        $this->attributeManager = $attributeManager;
    }

    /**
     * Configure and save index/list view for attributable entities
     * @param AdminContext $context
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function configureViewAction(AdminContext $context): Response
    {
        $event = new BeforeCrudActionEvent($context);
        try {
            $this->container->get('event_dispatcher')->dispatch($event);
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
        }
        if ($event->isPropagationStopped()) {
            return $event->getResponse();
        }

        if (!$this->isGranted(Permission::EA_EXECUTE_ACTION, ['action' => Action::DETAIL, 'entity' => null])) {
            throw new ForbiddenActionException($context);
        }

        $columns = [];
        $metaData = $this->getEntityManager()->getClassMetadata($this::getEntityFqcn());
        $fields = array_merge($metaData->fieldMappings, $metaData->associationMappings);
        $count = 1;
        foreach ($fields as $name => $field) {
            $columns[$name]['name'] = $name;
            $columns[$name]['label'] = $name;
            if (empty($field['element']['sortOrder'])) {
                $columns[$name]['sortOrder'] = $count++;
            } else {
                $columns[$name]['sortOrder'] = $field['element']['sortOrder'];
            }
        }
        $metaDataCategory = $this->getEntityManager()->getClassMetadata($metaData->getAssociationTargetClass('category'));
        $attributesClass = $metaDataCategory->getAssociationTargetClass('attributes');

        $dao = $this->getEntityManager()->getRepository($attributesClass);
        $attributes = $dao->findAll();
        /** @var AttributeInterface $attribute */
        foreach ($attributes as $attribute) {
            $key = $attribute->getUniqueKey();;
            $columns[$key]['name'] = $attribute->getUniqueKey();
            $columns[$key]['label'] = $attribute->getName();
            $columns[$key]['sortOrder'] = $attribute->getSortOrder();
        }

        uasort($columns, static function ($a, $b) {
            return $a['sortOrder'] > $b['sortOrder'];
        });
        $responseParameters = $this->configureResponseParameters(KeyValueStore::new([
            'pageName'     => 'configureView',
            'templateName' => 'crud/edit',
            'columns'      => $columns
        ]));

        $event = new AfterCrudActionEvent($context, $responseParameters);
        $this->container->get('event_dispatcher')->dispatch($event);
        if ($event->isPropagationStopped()) {
            return $event->getResponse();
        }
        return $this->render('bundles/EasyAdminBundle/crud/attribute/configure_view.html.twig', $responseParameters->all());
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
     * @return CategoryInterface
     */
    public function getCategory(): CategoryInterface
    {
        if ($this->category === null) {
            $entity = $this->getEntity();
            if ($entity instanceof AttributableEntity) {
                $category = $entity->getCategory();
                if ($category instanceof CategoryInterface) {
                    return $this->category = $category;
                }
            }

            $dao = $this->getEntityManager()->getRepository(ProductCategory::class);
            $this->category = $dao->getRootNodes()[0] ?? null;
            if ($this->category === null) {
                throw new InvalidArgumentException("The category tree root not exists. Please first create one.");
            }
        }
        return $this->category;
    }

    public function configureActions(Actions $actions): Actions
    {
        $configureView = Action::new('configureViewAction', 'Configure View', 'fa fa-columns')
            ->linkToCrudAction('configureViewAction')
            ->createAsGlobalAction();

        return $actions->add(Crud::PAGE_INDEX, $configureView);
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