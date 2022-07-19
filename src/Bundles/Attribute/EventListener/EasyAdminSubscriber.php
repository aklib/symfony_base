<?php
/**
 * Class EasyAdminSubscriber
 * @package App\Bundles\Attribute\EventListener
 *
 * since: 19.07.2022
 * author: alexej@kisselev.de
 */

namespace App\Bundles\Attribute\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EasyAdminSubscriber implements EventSubscriberInterface
{

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
//            BeforeEntityUpdatedEvent::class => ['beforeEntityUpdated'],
//            AfterCrudActionEvent::class => ['afterCrudActionEvent']
        ];
    }
}