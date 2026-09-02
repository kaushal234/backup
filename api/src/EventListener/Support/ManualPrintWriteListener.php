<?php

declare(strict_types=1);

namespace App\EventListener\Support;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Support\ManualPrint;
use App\Notifier\Support\ManualPrinterNotifier;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class ManualPrintWriteListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['generateZipAndSendRequestToPrinter', EventPriorities::POST_WRITE],
            ],
        ];
    }

    public function generateZipAndSendRequestToPrinter(ViewEvent $event): void
    {
        $manualPrint = $event->getControllerResult();
        $request = $event->getRequest();

        if ((!$manualPrint instanceof ManualPrint) || (!$request->isMethod(Request::METHOD_POST))) {
            return;
        }

        $this->serviceLocator->get(ManualPrinterNotifier::class)->sendEmail($manualPrint);
    }

    public static function getSubscribedServices(): array
    {
        return [
            ManualPrinterNotifier::class,
        ];
    }
}
