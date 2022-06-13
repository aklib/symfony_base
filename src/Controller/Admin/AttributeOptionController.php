<?php

namespace App\Controller\Admin;

use App\Entity\AttributeOption;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/attribute/option")
 */
class AttributeOptionController extends AbstractAppGrudController
{
    public static function getEntityFqcn(): string
    {
        return AttributeOption::class;
    }
}
