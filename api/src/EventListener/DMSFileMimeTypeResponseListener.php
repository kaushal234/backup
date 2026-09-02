<?php

declare(strict_types=1);

namespace App\EventListener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\DMS;
use LegacyBundle\Manager\DMSManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class DMSFileMimeTypeResponseListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => [['onDMSFileRead', EventPriorities::POST_RESPOND]],
        ];
    }

    public function onDMSFileRead(ResponseEvent $event)
    {
        $request = $event->getRequest();

        if ('dms' !== $request->getRequestFormat()) {
            return;
        }

        $dms = $event->getRequest()->attributes->get('data');

        if (!$dms instanceof DMS || !$event->getRequest()->isMethod(Request::METHOD_GET)) {
            return;
        }

        $file = $this->serviceLocator->get(DMSManager::class)->getDmsFile($dms->getLegacyId());
        $response = $event->getResponse();
        $response->headers->set('Content-Type', $file->getMimeType());
        $disposition = $response->headers->makeDisposition('inline', \sprintf('DMS_%s.%s', $dms->getLegacyId(), $file->getExtension()));
        $response->headers->set('Content-Disposition', $disposition);
    }

    public static function getSubscribedServices(): array
    {
        return [DMSManager::class];
    }
}
