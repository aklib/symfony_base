<?php

namespace App\Controller\Admin;

use App\Entity\UserProfile;

class UserProfileCrudController extends AbstractAttributableEntityCrudController
{
    public static function getEntityFqcn(): string
    {
        return UserProfile::class;
    }
}
