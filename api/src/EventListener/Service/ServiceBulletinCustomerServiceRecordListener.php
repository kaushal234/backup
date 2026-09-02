<?php

declare(strict_types=1);

namespace App\EventListener\Service;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Service\CustomerServiceRecord\AbstractCustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\ServiceBulletinCustomerServiceRecord;
use App\Event\EntityChangeEvent;
use LegacyBundle\Manager\CustomerServiceRecordManager;
use LegacyBundle\Manager\ModLogManager;
use LegacyBundle\Manager\ServiceBulletinLineManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class ServiceBulletinCustomerServiceRecordListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    public function __construct(
        private readonly ContainerInterface $container
    ) {
    }

    public static function getSubscribedServices(): array
    {
        return [
            CustomerServiceRecordManager::class,
            ServiceBulletinLineManager::class,
            ModLogManager::class,
        ];
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['preValidate', EventPriorities::PRE_VALIDATE],
            ],
            EntityChangeEvent::class => 'postWrite',
        ];
    }

    public function preValidate(ViewEvent $event): void
    {
        $serviceBulletinCustomerServiceRecord = $event->getControllerResult();

        if (!$serviceBulletinCustomerServiceRecord instanceof ServiceBulletinCustomerServiceRecord) {
            return;
        }

        $request = $event->getRequest();
        if (Request::METHOD_POST !== $request->getMethod()) {
            return;
        }

        $legacyServiceBulletinLine = $this->container->get(CustomerServiceRecordManager::class)->findServiceBulletinLine($serviceBulletinCustomerServiceRecord);

        if (false === $legacyServiceBulletinLine) {
            return;
        }

        $serviceBulletinCustomerServiceRecord->serviceBulletinLinesLegacyId = $legacyServiceBulletinLine['id'];
    }

    public function postWrite(EntityChangeEvent $event): void
    {
        $entity = $event->getChange()->getEntity();

        if (!$entity instanceof ServiceBulletinCustomerServiceRecord) {
            return;
        }

        $changes = $event->getChange()->getChangeSet();

        $isNewStatusCompleted = ($changes['status'][1] ?? null) === AbstractCustomerServiceRecord::COMPLETED;
        if (!$isNewStatusCompleted) {
            return;
        }

        /** @var ServiceBulletinLineManager $serviceBulletinLineManager */
        $serviceBulletinLineManager = $this->container->get(ServiceBulletinLineManager::class);
        $serviceBulletinLineManager->changeStatus($entity->serviceBulletinLinesLegacyId, 'CLOSED');

        /** @var ModLogManager $modLogManager */
        $modLogManager = $this->container->get(ModLogManager::class);
        $modLogManager->insertLog($entity->serviceBulletinLinesLegacyId, 'SBL', 'CLOSED');
    }
}
