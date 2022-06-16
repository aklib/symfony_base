<?php

namespace App\Controller\Admin;

use App\Entity\User;

class UserCrudController extends AbstractAppGrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }
}
