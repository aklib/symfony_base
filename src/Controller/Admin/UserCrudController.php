<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\UserProfile;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;

class UserCrudController extends AbstractAppGrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function excludeFields(string $pageName = 'index'): array
    {
        $fields = parent::excludeFields($pageName);
        if($pageName !== 'index'){
            $fields[] = 'userProfile';
        }
        return $fields;
    }

    public function edit(AdminContext $context)
    {
        $entity = $context->getEntity()->getInstance();
        if($entity instanceof User){
            $this->createProfile($entity);
        }
        return parent::edit($context);
    }

    private function createProfile(User $user): void
    {
        $profile = $user->getUserProfile();
        if($profile === null){
            $profile = new UserProfile();
            $profile->setUser($user);
            $this->getEntityManager()->persist($profile);
        }
    }
}
