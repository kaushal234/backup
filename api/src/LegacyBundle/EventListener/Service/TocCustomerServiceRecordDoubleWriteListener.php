<?php

declare(strict_types=1);

namespace LegacyBundle\EventListener\Service;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Service\CustomerServiceRecord\TechnicianOnCallCustomerServiceRecord;
use App\Entity\Service\TechnicianOnCall;
use LegacyBundle\Manager\ModLinkManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class TocCustomerServiceRecordDoubleWriteListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    public function __construct(
        private readonly ContainerInterface $container
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['onCreation', EventPriorities::POST_WRITE],
            ],
        ];
    }

    public static function getSubscribedServices(): array
    {
        return [
            ModLinkManager::class,
        ];
    }

    public function onCreation(ViewEvent $event): void
    {
        $tocCustomerServiceRecord = $event->getControllerResult();

        if (!$tocCustomerServiceRecord instanceof TechnicianOnCallCustomerServiceRecord) {
            return;
        }

        $request = $event->getRequest();

        if (Request::METHOD_POST !== $request->getMethod()) {
            return;
        }

        $this->container->get(ModLinkManager::class)->createLink(
            $tocCustomerServiceRecord->tocLegacyId, TechnicianOnCall::MODULE_NAME,
            $tocCustomerServiceRecord->getLegacyId(), 'CSR'
        );
    }
}
