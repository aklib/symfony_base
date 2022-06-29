<?php
/**
 * Class AttributeManagerParentChild
 * @package App\Bundles\Attribute\Manager
 *
 * since: 29.06.2022
 * author: alexej@kisselev.de
 */

namespace App\Bundles\Attribute\Manager;

use App\Bundles\Attribute\Entity\AttributableEntity;
use FOS\ElasticaBundle\Elastica\Index;

class AttributeManagerParentChild extends AbstractAttributeManager
{


    public function flush(): void
    {
        // TODO: Implement flush() method.
    }

    public function getAttributeValues(AttributableEntity $entity): array
    {
        return [];
    }

    protected function getIndex(): Index
    {
        return $this->getIndexManager()->getIndex('parent_child');
    }
}