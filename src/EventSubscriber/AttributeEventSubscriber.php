<?php

namespace App\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\HttpKernel\Event\ViewEvent;

class AttributeEventSubscriber implements EventSubscriber
{
    public function postLoad(LifecycleEventArgs $eventArgs): void
    {
//        die(__CLASS__ . ':' .__FUNCTION__);
    }

    public function prePersist(LifecycleEventArgs $eventArgs): void
    {
//        die(__CLASS__ . ':' .__FUNCTION__);
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $classMetadata = $eventArgs->getClassMetadata();
        //dump($classMetadata);
       /* foreach ($classMetadata->getReflectionClass()->getProperties() as $property) {
            foreach ($this->reader->getPropertyAnnotations($property) as $annotation) {
                if ($annotation instanceof DoctrineEventSubscriberInterface) {
                    if ($classMetadata->hasAssociation($property->getName())) {
                        $classMetadata->associationMappings[$property->getName()][$annotation->getUniqueKey()] = (array)$annotation;
                    } else {
                        $classMetadata->fieldMappings[$property->getName()][$annotation->getUniqueKey()] = (array)$annotation;
                    }
                }
            }
        }*/
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::loadClassMetadata,
            Events::postLoad,
            Events::prePersist,
        ];
    }


    public function onKernelView(ViewEvent $event): void
    {

    }

}
