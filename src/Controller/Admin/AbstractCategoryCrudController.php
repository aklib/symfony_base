<?php
/**
 * Class AbstractCategoryController
 * @package App\Controller\Admin
 *
 * since: 07.07.2022
 * author: alexej@kisselev.de
 */

namespace App\Controller\Admin;

use App\Bundles\Attribute\Controller\CrudControllerManager;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractCategoryCrudController extends AbstractAppGrudController
{
    protected AdminUrlGenerator $adminUrlGenerator;

    public function __construct(EntityManagerInterface $em, CrudControllerManager $controllerManager, AdminUrlGenerator $adminUrlGenerator)
    {
        parent::__construct($em, $controllerManager);
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions->disable('batchDelete');
        return parent::configureActions($actions);
    }

    public function configureCrud(Crud $crud): Crud
    {
        // handle nestedset
        return parent::configureCrud($crud)
            ->setDefaultSort(['lft' => 'ASC'])->setPaginatorPageSize(1000)
            ->overrideTemplate('crud/index', 'bundles/EasyAdminBundle/crud/category/index.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = (array)parent::configureFields($pageName);

        if ($pageName === 'index') {
            /** @var FieldTrait $field */
            foreach ($fields as $field) {
                $field->setSortable(false);
            }
        }
        if (array_key_exists('attributes', $fields) && $fields['attributes'] instanceof AssociationField) {
            $fields['attributes']->setTemplatePath('bundles/EasyAdminBundle/crud/field/attribute_list_accordion.html.twig')->hideOnForm();
        }
        return $fields;
    }

    public function configureAssets(Assets $assets): Assets
    {
        return parent::configureAssets($assets)
            ->addCssFile('js/jquery-treegrid/dist/css/jquery.treegrid.css')
            ->addJsFile('/js/jquery-3.1.1.min.js')
            ->addJsFile('/js/jquery-treegrid/dist/js/jquery.treegrid.js');
    }

    public function excludeFields(string $pageName = null): array
    {
        $fields = array_merge(parent::excludeFields($pageName), ['lft', 'rgt', 'level', 'root', 'children']);
        if ($pageName !== 'index') {
            $fields[] = 'products';
        }
        return $fields;
    }

    public function reorderAction(Request $request): Response
    {
        // post
        $dataRaw = [
            'node'   => (int)$request->get('node'),
            'parent' => (int)$request->get('parent'),
            'after'  => (int)$request->get('after'),
            'before' => (int)$request->get('before'),
        ];

        $data = array_filter($dataRaw, static fn($value) => !empty($value));
        $response = [
            'success' => true
        ];

        /** @var NestedTreeRepository $dao */
        $dao = $this->getEntityManager()->getRepository($this::getEntityFqcn());
        $node = $dao->find($data['node']);
        if ($node !== null) {
            if (array_key_exists('parent', $data)) {
                $parent = $dao->find($data['parent']);
                if ($parent !== null) {
                    $dao->persistAsFirstChildOf($node, $parent);
                } else {
                    $response['success'] = false;
                    $response['message'] = sprintf('Parent node#%d not found', $data['parent']);
                }
            }
            if (array_key_exists('after', $data)) {
                $sibling = $dao->find($data['after']);
                if ($sibling !== null) {
                    $dao->persistAsNextSiblingOf($node, $sibling);
                } else {
                    $response['success'] = false;
                    $response['message'] = sprintf('Node#%d not found', $data['after']);
                }
            } elseif (array_key_exists('before', $data)) {
                $sibling = $dao->find($data['before']);
                if ($sibling !== null) {
                    $dao->persistAsPrevSiblingOf($node, $sibling);
                } else {
                    $response['success'] = false;
                    $response['message'] = sprintf('Node#%d not found', $data['before']);
                }
            }
        } else {
            $response['success'] = false;
            $response['message'] = 'Node not found';
        }
        if ($response['success']) {
            $this->getEntityManager()->flush();
        } else {
            $this->addFlash('warning', $response['message']);
        }
        return new JsonResponse($response);
    }
}