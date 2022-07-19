<?php /** @noinspection PhpUnused */

/**
 * Class AbstractAppGrudController
 * @package App\Controller\Admin
 *
 * since: 25.05.2022
 * author: alexej@kisselev.de
 */

namespace App\Controller\Admin;

use App\Bundles\Attribute\Controller\CrudControllerManager;
use App\Controller\Admin\Extension\CrudControllerManagerInterface;
use App\Entity\Extension\DeletableEntity;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

abstract class AbstractAppGrudController extends AbstractCrudController implements CrudControllerManagerInterface
{
    private EntityManagerInterface $em;
    private CrudControllerManager $controllerManager;
    private AdminUrlGenerator $adminUrlGenerator;

    public function __construct(EntityManagerInterface $em, CrudControllerManager $controllerManager, AdminUrlGenerator $adminUrlGenerator)
    {
        $this->em = $em;
        $this->controllerManager = $controllerManager;
        $this->adminUrlGenerator = $adminUrlGenerator;
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
        return $this->getControllerManager()->configureFields($this, $pageName, $this->getFieldOptions($pageName));
    }

    public function configureFilters(Filters $filters): Filters
    {
        foreach ($this->getControllerManager()->getMappings($this) as $propertyName => $mapping) {
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
        // create icon buttons
        $actions->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
            return $action->addCssClass('btn btn-icon btn-sm btn-default')
                ->setIcon('fa fa-pencil')
                ->setLabel(false)
                ->setHtmlAttributes(['title' => 'Edit']);
        })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->addCssClass('btn btn-icon btn-sm btn-default')
                    ->setIcon('fa fa-trash')
                    ->setLabel(false)
                    ->setHtmlAttributes(['title' => 'Delete'])
                    ->displayIf(static function ($entity) {
                        if ($entity instanceof DeletableEntity) {
                            return $entity->isDeletable();
                        }
                        return true;
                    });
            })
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->addCssClass('btn btn-icon btn-sm btn-default')
                    ->setIcon('fa fa-eye')
                    ->setLabel(false)
                    ->setHtmlAttributes(['title' => 'Details']);
            })
            ->reorder(Crud::PAGE_INDEX, [Action::EDIT, Action::DETAIL, Action::DELETE]);
        return $actions;
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->em;
    }

    /**
     * @return AdminUrlGenerator
     */
    public function getAdminUrlGenerator(): AdminUrlGenerator
    {
        return $this->adminUrlGenerator;
    }

    /**
     * @return CrudControllerManager
     */
    public function getControllerManager(): CrudControllerManager
    {
        return $this->controllerManager;
    }

    public function getFieldOptions(string $pageName = 'index'): array
    {
        return [];
    }

    protected function getEntity(): ?object
    {
        if ($this->getContext() !== null && $this->getContext()->getEntity() instanceof EntityDto) {
            return $this->getContext()->getEntity()->getInstance();
        }
        return null;
    }
}