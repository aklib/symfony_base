<?php
/**
 * Class AttributeManagerDatabase
 * @package App\Bundles\Attribute\Manager
 *
 * since: 01.07.2022
 * author: alexej@kisselev.de
 */

namespace App\Bundles\Attribute\Manager;

use App\Bundles\Attribute\Entity\AttributableEntity;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;

class AttributeManagerDatabase extends AbstractAttributeManager
{
    public function search(QueryBuilder $qb, SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields): void
    {
        // TODO: Implement search() method.
    }

    public function flush(): void
    {
        // TODO: Implement flush() method.
    }

    public function getAttributeValues(AttributableEntity $entity): array
    {
        return [];
    }
}