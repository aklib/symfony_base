<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\UserProfile;

class UserProfileCrudController extends AbstractAttributableEntityController
{
    public static function getEntityFqcn(): string
    {
        return UserProfile::class;
    }

    /** @noinspection PhpUndefinedMethodInspection */
    public function getCategory(): ?Category
    {
        if($this->category === null){
            $this->category = $this->getEntityManager()->getRepository(Category::class)->findOneByUniqueKey('user');
        }
        return $this->category;
    }
}
