<?php

namespace App\EventSubscriber;

use App\Entity\Extension\Annotation\CustomDoctrineAnnotation;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\HttpKernel\Event\ViewEvent;

class AttributeEventSubscriber implements EventSubscriber
{

    private AnnotationReader $reader;

    public function __construct()
    {
        $this->reader = new AnnotationReader();
    }

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
        foreach ($classMetadata->getReflectionClass()->getProperties() as $property) {
            foreach ($this->reader->getPropertyAnnotations($property) as $annotation) {
                if ($annotation instanceof CustomDoctrineAnnotation) {
                    $key = strtolower(substr(strrchr(get_class($annotation), "\\"), 1));
                    if ($classMetadata->hasAssociation($property->getName())) {
                        $classMetadata->associationMappings[$property->getName()][$key] = (array)$annotation;
                    } else {
                        $classMetadata->fieldMappings[$property->getName()][$key] = (array)$annotation;
                    }
                }
            }
        }
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
