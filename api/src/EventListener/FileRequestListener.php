<?php

declare(strict_types=1);

namespace App\EventListener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class FileRequestListener implements EventSubscriberInterface
{
    public function onFileFormatRequest(ResponseEvent $event)
    {
        $request = $event->getRequest();
        $format = $request->getRequestFormat();

        if (!\in_array($format, ['csv', 'xlsx'], true)) {
            return;
        }

        $response = $event->getResponse();
        $disposition = $response->headers->makeDisposition('inline', \sprintf('data.%s', $format));
        $response->headers->set('Content-Disposition', $disposition);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => ['onFileFormatRequest', EventPriorities::POST_RESPOND],
        ];
    }
}
