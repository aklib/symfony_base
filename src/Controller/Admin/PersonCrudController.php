<?php

namespace App\Controller\Admin;

use App\Entity\Person;

class PersonCrudController extends AbstractAttributableEntityCrudController
{
    public static function getEntityFqcn(): string
    {
        return Person::class;
    }
}
