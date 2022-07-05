<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\UserProfile;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;

class UserCrudController extends AbstractAppGrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function excludeFields(string $pageName = 'index'): array
    {
        $fields = parent::excludeFields($pageName);
        if ($pageName !== 'index') {
            $fields[] = 'userProfile';
        }
        return $fields;
    }

    public function edit(AdminContext $context)
    {
        $entity = $context->getEntity()->getInstance();
        if ($entity instanceof User) {
            $this->createProfile($entity);
        }
        return parent::edit($context);
    }

    public function profile(AdminUrlGenerator $adminUrlGenerator): RedirectResponse
    {
        /** @var User $entity */
        $entity = $this->getEntity();
        if ($entity instanceof User && $entity->getUserProfile() === null) {
            $this->addFlash('warning', 'No user Profile found');
            return new RedirectResponse($adminUrlGenerator->setAction(Crud::PAGE_INDEX)->generateUrl());
        }

        if($entity === null) {
            $this->addFlash('warning', 'No user found');
            return new RedirectResponse($adminUrlGenerator->setAction(Crud::PAGE_INDEX)->generateUrl());
        }

        $adminUrlGenerator
            ->setController(UserProfileEntityController::class)
            ->setAction(Crud::PAGE_DETAIL)
            ->setEntityId($entity->getUserProfile()->getId());
        return new RedirectResponse($adminUrlGenerator->generateUrl());
    }

    private function createProfile(User $user): void
    {
        $profile = $user->getUserProfile();
        if ($profile === null) {
            $profile = new UserProfile();
            $profile->setUser($user);
            $this->getEntityManager()->persist($profile);
        }
    }

    public function configureActions(Actions $actions): Actions
    {

        $viewProfile = Action::new('View Invoice', false)
            ->displayIf(static function (User $user) {
                return $user->getUserProfile() !== null;
            })
            ->addCssClass('btn btn-icon btn-sm btn-default')
            ->setIcon('far fa-address-card')
            ->setHtmlAttributes(['title' => 'View Profile'])
            ->linkToCrudAction('profile');

        $actions->add(Crud::PAGE_INDEX, $viewProfile);
        return parent::configureActions($actions);
    }
}
