<?php
/**
 * Class AbstractAppGrudController
 * @package App\Controller\Admin
 *
 * since: 25.05.2022
 * author: alexej@kisselev.de
 */

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

abstract class AbstractAppGrudController extends AbstractCrudController
{
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
     * Customise action icons
     * @param Actions $actions
     * @return Actions
     */
    public function configureActions(Actions $actions): Actions
    {
        $actions->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action){
            return $action->addCssClass('btn btn-icon btn-sm btn-default')->setIcon('fa fa-pencil')->setLabel(false)->setHtmlAttributes(['title' => 'Edit']);
        })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action){
            return $action->addCssClass('btn btn-icon btn-sm btn-default')->setIcon('fa fa-trash')->setLabel(false)->setHtmlAttributes(['title' => 'Delete']);
        });
        return $actions;
    }
}