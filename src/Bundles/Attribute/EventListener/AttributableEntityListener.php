<?php /** @noinspection PhpUnused */

/** @noinspection DuplicatedCode */

namespace App\Bundles\Attribute\EventListener;

use App\Bundles\Attribute\Adapter\Interfaces\AttributeEntityAdapterInterface;
use App\Entity\Extension\Annotation\CustomDoctrineAnnotation;
use App\Entity\Extension\Attributable\AttributableEntity;
use App\Entity\Extension\Attributable\AttributeInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;

class AttributableEntityListener implements EventSubscriber
{
    private AnnotationReader $reader;
    private AttributeEntityAdapterInterface $attributeManager;

    public function __construct(AttributeEntityAdapterInterface $attributeManager)
    {
        $this->reader = new AnnotationReader();
        $this->attributeManager = $attributeManager;

    }

    //============================ DOCTRINE ============================

    public function getSubscribedEvents(): array
    {
        return [
            Events::loadClassMetadata,
            Events::postLoad,
            Events::preUpdate,
            Events::postFlush,
            Events::postPersist,
            Events::preRemove
        ];
    }

    public function preUpdate(LifecycleEventArgs $eventArgs): void
    {

    }

    /**
     * It will be invoked after the database insert operations.
     * @param LifecycleEventArgs $eventArgs
     * @return void
     */
    public function postPersist(LifecycleEventArgs $eventArgs): void
    {
        $this->postLoad($eventArgs);
    }

    public function preRemove(LifecycleEventArgs $eventArgs): void
    {
        $entity = $eventArgs->getEntity();
        if ($entity instanceof AttributableEntity) {
            $this->attributeManager->removeEntity($entity);
        }
    }

    public function postLoad(LifecycleEventArgs $eventArgs): void
    {
        $entity = $eventArgs->getEntity();
        if ($entity instanceof AttributableEntity) {
            // entities are loaded in controller fqcn
            $this->attributeManager->addEntity($entity);
        } elseif ($entity instanceof AttributeInterface) {
            // attributes are loaded in controller ->getCategory()->getAttributes()
            $this->attributeManager->addAttribute($entity);
        }
    }

    /**
     * Save modified entity attributes
     * @param PostFlushEventArgs $eventArgs
     * @return void
     * @noinspection PhpUnusedParameterInspection
     */
    public function postFlush(PostFlushEventArgs $eventArgs): void
    {
        // save attribute values
        $this->attributeManager->flush();
    }

    //============================ CUSTOM ANNOTATIONS ============================

    /** @noinspection PhpPossiblePolymorphicInvocationInspection */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $classMetadata = $eventArgs->getClassMetadata();
        $refClass = $classMetadata->getReflectionClass();
        if ($refClass !== null) {
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
    }
}
