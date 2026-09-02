<?php

declare(strict_types=1);

namespace LegacyBundle\EventListener\Service;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Service\CustomerServiceRecord\ServiceBulletinCustomerServiceRecord;
use LegacyBundle\Manager\ServiceBulletinLineManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class ServiceBulletinCustomerServiceRecordDoubleWriteListener implements EventSubscriberInterface, ServiceSubscriberInterface
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
            ServiceBulletinLineManager::class,
        ];
    }

    public function onCreation(ViewEvent $event): void
    {
        $serviceBulletinCustomerServiceRecord = $event->getControllerResult();

        if (!$serviceBulletinCustomerServiceRecord instanceof ServiceBulletinCustomerServiceRecord) {
            return;
        }

        $request = $event->getRequest();

        if (Request::METHOD_POST !== $request->getMethod()) {
            return;
        }

        $this->container->get(ServiceBulletinLineManager::class)->addCustomerServiceRecord($serviceBulletinCustomerServiceRecord);
    }
}
