<?php

namespace App\Entity\Extension\Traits;


use App\Entity\Category;
use Doctrine\Common\Collections\Collection;
use FlorianWolters\Component\Core\StringUtils;


/**
 * Class AttributeEntityTrait
 *
 * since: 16.09.2021
 * author: alexej@kisselev.de
 */
trait AttributableEntityTrait
{
    private ?array $attributeValues = null;

    private function decamelize($string): string
    {
        return strtolower(preg_replace(['/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/'], '$1_$2', $string));
    }

    /**
     * @param $getter
     * @param $args
     * @return array|string|string[]|null
     * @noinspection PhpUnusedParameterInspection
     */
    private function get($getter, $args)
    {
        $attributeValues = $this->getAttributeValues();
        $name = lcfirst(preg_replace('/^get/', '', $getter));
        if (array_key_exists($name, $attributeValues)) {
            return $attributeValues[$name];
        }
        $name = $this->decamelize($name);
        return $attributeValues[$name] ?? null;
    }

    public function __call($name, $args)
    {
        if (StringUtils::startsWith($name, 'get')) {
            return $this->get($name, $args);
        }
        return null;
    }

    final public function getAttributeValues(): array
    {
        if ($this->attributeValues === null) {
            $this->attributeValues = [];
            // Attribute\Event\AttributeListener
            //---------temp $this->getEventManager()->trigger(Constant::EVENT_GET_ATTRIBUTE_VALUES, $this);
        }
        return $this->attributeValues;
    }

    public function getAttributes(): Collection
    {
        return $this->getCategory()->getAttributes(true);
    }

    public function toArray(): array
    {
        $array['id'] = $this->getId();
        if(method_exists($this, 'getName')){
            $array['name'] = $this->getName();
        }
        $array['category']['id'] = $this->getCategory()->getId();
        $array['category']['name'] = $this->getCategory()->getName();
        if(method_exists($this, 'getStatus')){
            $array['status']['id'] = $this->getStatus()->getId();
            $array['status']['name'] = $this->getStatus()->getName();
        }
        return array_merge_recursive($array, $this->addArray());
    }


    /**
     * @param array $attributeValues
     */
    public function setAttributeValues(array $attributeValues): void
    {
        $this->attributeValues = $attributeValues;
    }

    abstract public function getId(): int;

    abstract public function getCategory(): Category;
    abstract protected function addArray(): array;
}