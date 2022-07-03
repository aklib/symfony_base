<?php /** @noinspection PhpUnusedLocalVariableInspection */
/** @noinspection PhpUnused */

/**
 * Class EasyAdminSubscriber
 * @package App\Bundles\EasyAdminBundle\EventListener
 *
 * since: 03.07.2022
 * author: alexej@kisselev.de
 */

namespace App\Bundles\EasyAdminBundle\EventListener;

use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeCrudActionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class EasyAdminSubscriber implements EventSubscriberInterface
{


    public function __construct()
    {

    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeCrudActionEvent::class => ['beforeCrudActionEvent'],
            KernelEvents::CONTROLLER     => 'onKernelController',
        ];
    }

    public function beforeCrudActionEvent(BeforeCrudActionEvent $event): void
    {


    }

    public function onKernelController(ControllerEvent $event): void
    {
        //$this->session->getFlashBag()->add('success', 'Class: ' . $event->getController());

    }
}