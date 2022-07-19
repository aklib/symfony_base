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

use App\Bundles\Attribute\Adapter\Interfaces\AttributeAdapterInterface;
use App\Bundles\Attribute\Controller\CrudControllerAttributableEntity;
use App\Bundles\Attribute\Controller\CrudControllerManager;
use App\Bundles\Attribute\Manager\ViewConfigManager;
use App\Entity\Extension\Attributable\AttributableEntity;
use App\Entity\Extension\Attributable\AttributeInterface;
use App\Entity\Extension\Attributable\CategoryInterface;
use App\Entity\Product\ProductCategory;
use App\Entity\User;
use App\Entity\UserViewConfig;
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
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
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
    protected ViewConfigManager $viewConfigManager;

    public function __construct(EntityManagerInterface $em, CrudControllerManager $controllerManager, AdminUrlGenerator $adminUrlGenerator, AttributeAdapterInterface $attributeManager, ViewConfigManager $viewConfigManager)
    {
        parent::__construct($em, $controllerManager, $adminUrlGenerator);
        $this->attributeManager = $attributeManager;
        $this->viewConfigManager = $viewConfigManager;
        $this->viewConfigManager->setEntityFqcn($this::getEntityFqcn());
    }

    public function getFieldOptions(string $pageName = 'index'): array
    {
        $fields = parent::getFieldOptions($pageName);
        $config = $this->getViewConfigManager()->getCurrentViewConfig($pageName);
        if ($config instanceof UserViewConfig) {
            $fields = array_replace_recursive($fields, $config->getColumnOptions());
        }
        return $fields;
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
        // ============== GET CONFIG ==============
        $currentConfig = $this->getViewConfigManager()->getCurrentViewConfig();
        if ($currentConfig === null) {
            $currentConfig = $this->getViewConfigManager()->createViewConfig();
        }
        // ============== GET FIELDS ==============
        // 1. from class
        $metaData = $this->getEntityManager()->getClassMetadata($this::getEntityFqcn());
        $fields = array_merge($metaData->fieldMappings, $metaData->associationMappings);
        $columns = $currentConfig->getColumnOptions();
        $sortOrder = 1;
        foreach ($fields as $name => $field) {
            $columns[$name]['name'] = $name;
            $columns[$name]['label'] = $name;
            if (!array_key_exists('sortOrder', $columns[$name])) {
                if (array_key_exists('element', $field)) {
                    $columns[$name]['sortOrder'] = $field['element']['sortOrder'] ?? 100;
                } else {
                    $columns[$name]['sortOrder'] = $sortOrder;
                }
            }
            if (!array_key_exists('visible', $columns[$name])) {
                $columns[$name]['visible'] = true;
            }
            $sortOrder++;
        }

        // 2. from attributes
        $metaDataCategory = $this->getEntityManager()->getClassMetadata($metaData->getAssociationTargetClass('category'));
        $attributesClass = $metaDataCategory->getAssociationTargetClass('attributes');

        $dao = $this->getEntityManager()->getRepository($attributesClass);
        $attributes = $dao->findAll();
        /** @var AttributeInterface $attribute */
        foreach ($attributes as $attribute) {
            $name = $attribute->getUniqueKey();
            $columns[$name]['name'] = $attribute->getUniqueKey();
            $columns[$name]['label'] = $attribute->getName();
            if (!array_key_exists('sortOrder', $columns[$name])) {
                $columns[$name]['sortOrder'] = $attribute->getSortOrder();
            }
            if (!array_key_exists('visible', $columns[$name])) {
                $columns[$name]['visible'] = true;
            }
        }

        uasort($columns, static function ($a, $b) {
            return $a['sortOrder'] > $b['sortOrder'];
        });

        // ============== NO FORM HANDLING ==============

        $responseParameters = $this->configureResponseParameters(KeyValueStore::new([
            'pageName'      => 'configureView',
            'entity_label'  => ucfirst($this->getScope()),
            'templateName'  => 'crud/edit',
            'columns'       => $columns,
            'currentConfig' => $currentConfig
        ]));

        $request = $context->getRequest();
        if ($request->isMethod('POST')) {
            $attributeMap = [];
            /** @var AttributeInterface $attribute */
            foreach ($attributes as $attribute) {
                $attributeMap[$attribute->getUniqueKey()] = $attribute;
            }
            $columnOptions = $request->request->all();

            // ============== MODIFY ATTRIBUTE SORT ORDER ==============
            foreach ($columnOptions as $name => $column) {
                if (array_key_exists($name, $attributeMap)) {
                    $attributeMap[$name]->setSortOrder((int)$column['sortOrder']);
                    $this->getEntityManager()->persist($attributeMap[$name]);
                }
            }
            if ($columnOptions['submit'] === 'create') {
                $currentConfig = $this->getViewConfigManager()->createViewConfig();
                $user = $this->getUser();
                if ($user instanceof User) {
                    foreach ($user->getUserViewConfigs() as $userViewConfig) {
                        $userViewConfig->setCurrent(false);
                        $this->getEntityManager()->persist($userViewConfig);
                    }
                }
            }
            $currentConfig->setName($columnOptions['config_name'] ?? 'default');
            unset($columnOptions['config_name'], $columnOptions['submit']);
            $currentConfig->setColumnOptions($columnOptions);
            $this->getEntityManager()->persist($currentConfig);
            $this->getEntityManager()->flush();
            $url = $this->getAdminUrlGenerator()->setAction(Crud::PAGE_INDEX)->setController(get_class($this))->generateUrl();
            return $this->redirect($url);
        }

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
        parent::configureActions($actions);
        $viewConfig = $this->getViewConfigManager()->getCurrentViewConfig();
        $label = $viewConfig !== null ? 'Config: ' . $viewConfig->getName() : 'Configure View';
        $configureView = Action::new('configureViewAction', $label, 'fa fa-columns')
            ->setTemplatePath('bundles/EasyAdminBundle/crud/attribute/configure_view_button.html.twig')
            ->linkToCrudAction('configureViewAction')
            ->createAsGlobalAction();
        return $actions->add(Crud::PAGE_INDEX, $configureView);
    }

    /**
     * Gets entity class name in a snake case e.g. App\Entity\UserProfile to user_profile
     * @param string|null $entityFqcn
     * @return string
     * @noinspection PhpSameParameterValueInspection
     */
    private function getScope(string $entityFqcn = null): string
    {
        if ($entityFqcn === null) {
            $entityFqcn = $this::getEntityFqcn();
        }
        return Util::toSnakeCase(substr(strrchr($entityFqcn, '\\'), 1));
    }

    /**
     * @return ViewConfigManager
     */
    public function getViewConfigManager(): ViewConfigManager
    {
        return $this->viewConfigManager;
    }


}