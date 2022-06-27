<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

/**
 * Class ProductCrudController
 * @package App\Controller\Admin
 *
 * @since: 09.06.2022
 * @author: alexej@kisselev.de
 */
class ProductCrudController extends AbstractAttributableEntityCrudController
{

    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)->setPaginatorPageSize(50);
    }


//    public function index(AdminContext $context)
//    {
//        dump(Util::toSnakeCase(substr(strrchr(UserProfile::class, '\\'), 1)));
//die;
//        $q = new Query();
//        $bool = new BoolQuery();
//        $q->setQuery($bool);
//        $term = new Term(['scope.keyword' => 'product']);
//        $bool->addMust($term);
//
//
//        $nested = new Nested();
//        $nested->setPath('attributes');
//        $boolQuery = new BoolQuery();
//
//        $queryString = new QueryString('anna');
//
//        $boolQuery->addMust($queryString);
//        $nested->setQuery($boolQuery);
//
//        $bool->addMust($nested);
//
//        $terms = new Terms('scope');
//        $terms->setField('id');
//        $q->addAggregation($terms);
//
//        $result = $this->indexManager->getDefaultIndex()->search($q);
//        $this->printQuery($q);
//
//        return parent::index($context);
//    }


}


