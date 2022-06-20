<?php

namespace App\Controller\Admin;

use App\Entity\UserProfile;

class UserProfileCrudController extends AbstractAppGrudController
{
    public static function getEntityFqcn(): string
    {
        return UserProfile::class;
    }



//    public function edit(AdminContext $context)
//    {
//        try {
//            return parent::edit($context);
//        } catch (Exception $e) {
//            die('popal');
//        }
//        $entity = $context->getEntity()->getInstance();
//        if ($entity === null) {
//            $url = $this->adminUrlGenerator->setAction('new')->setController(__CLASS__)->generateUrl();
//            return new RedirectResponse($url);
//        }
//        return parent::edit($context);
//    }
}
