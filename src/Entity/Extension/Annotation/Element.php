<?php

namespace App\Entity\Extension\Annotation;

use App\Bundles\Attribute\Constant;
use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 */
class Element implements CustomDoctrineAnnotation
{
    /**
     * Form element class full name
     * @var string
     */
    public string $type;
    /**
     * Attribute tab uniqueKey
     * @var string
     */
    public string $tab;

    /**
     * @var string
     */
    public string $help;

    /**
     * @var integer
     */
    public int $sortOrder;

    public function __construct(array $properties)
    {
        $this->type = $properties['type'] ?? '';
        $this->tab = $properties[Constant::OPTION_TAB] ?? 'general';
        $this->help = $properties['help'] ?? '';
        $this->sortOrder = $properties[Constant::OPTION_SORT_ORDER] ?? 100;
    }
}
