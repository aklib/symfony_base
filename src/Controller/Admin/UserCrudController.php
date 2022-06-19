<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;

class UserCrudController extends AbstractAppGrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }


    public function profile(AdminContext $context)
    {
        $entity = $context->getEntity();
        $user = $this->getUser();
        dump(
            $user === $entity->getInstance()
        );
        die;
        return $this->edit($context);
    }
}
