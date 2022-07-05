<?php

namespace App\Controller\Admin;

use App\Entity\UserProfile;

class UserProfileEntityController extends AbstractAttributableEntityController
{
    public static function getEntityFqcn(): string
    {
        return UserProfile::class;
    }
}
