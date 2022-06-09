<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ProductCrudController  * @package App\Controller\Admin
 *
 * @since: 09.06.2022
 * @author: alexej@kisselev.de
 */
class ProductCrudController extends AbstractAppGrudController
{


    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    /**
     *
     * @param AdminContext $context
     * @return KeyValueStore
     */
    public function index(AdminContext $context): KeyValueStore
    {
//        dump($context->getEntity());
        /** @var KeyValueStore $map */
        $map = parent::index($context);
        $entities = $map->get('entities');
        /** @var EntityDto $entityDto */
        foreach ($entities as $entityDto) {
            /** @var Product $product */
            $product = $entityDto->getInstance();
            $attributes = $product->getCategory()->getAttributesRecursive();
//            dump($attributes->count());
        }

        return $map;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {

        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
//dump($qb->getDQL());
        return $qb;
    }
}
