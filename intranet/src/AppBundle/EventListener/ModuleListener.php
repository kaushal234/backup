<?php

declare(strict_types=1);

namespace AppBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ModuleListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        if (null !== ($module = $event->getRequest()->attributes->get('alvest_module'))) {
            $event->getResponse()->headers->set('X-Alvest-Module', $module);
        }
    }
}
