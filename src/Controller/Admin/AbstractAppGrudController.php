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
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractAppGrudController extends AbstractCrudController implements CrudControllerManagerInterface
{
    private EntityManagerInterface $em;
    private TranslatorInterface $translator;
    private CrudControllerManager $controllerManager;
    protected const OPTION_SORT_ORDER = 'sortOrder';

    public function __construct(EntityManagerInterface $em, TranslatorInterface $translator, CrudControllerManager $controllerManager)
    {
        $this->em = $em;
        $this->translator = $translator;
        $this->controllerManager = $controllerManager;
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
        return $this->getControllerManager()->configureFields($this, $pageName, $this->excludeFields($pageName));
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
                    ->setHtmlAttributes(['title' => 'Delete']);
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

    /**
     * @return CrudControllerManager
     */
    public function getControllerManager(): CrudControllerManager
    {
        return $this->controllerManager;
    }

    public function excludeFields(string $pageName = 'index'): array
    {
        $fields = [];
        if($pageName === 'new' || $pageName === 'edit'){
            $fields[] = 'createdBy';
            $fields[] = 'updatedBy';
            $fields[] = 'createdAt';
            $fields[] = 'updatedAt';
        }
        return $fields;
    }

    protected function getEntity(): ?object
    {
        if ($this->getContext() !== null && $this->getContext()->getEntity() instanceof EntityDto) {
            return $this->getContext()->getEntity()->getInstance();
        }
        return null;
    }
}