<?php

declare(strict_types=1);

namespace AppBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class MSLinkHandlerListener implements EventSubscriberInterface
{
    public function onKernelRequest(RequestEvent $event)
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        if (null === $userAgent = $request->headers->get('User-Agent')) {
            return;
        }

        if (!preg_match('/[^\w](Word|Excel|PowerPoint|ms-office)([^\w]|\z)/', $userAgent)) {
            return;
        }

        $event->setResponse(new Response('<html><head><meta http-equiv="refresh" content="0"/></head><body></body></html>'));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 255],
        ];
    }
}
