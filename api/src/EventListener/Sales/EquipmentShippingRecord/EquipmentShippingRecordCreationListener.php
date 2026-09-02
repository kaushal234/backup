<?php

declare(strict_types=1);

namespace App\EventListener\Sales\EquipmentShippingRecord;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecord;
use App\Notifier\Sales\EquipmentShippingRecord\EquipmentShippingRecordNotifier;
use LegacyBundle\Manager\EquipmentRecordManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class EquipmentShippingRecordCreationListener implements EventSubscriberInterface, ServiceSubscriberInterface
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
                ['updateLegacyEquipmentRecord', EventPriorities::POST_WRITE],
                ['newEquipmentShippingRecordNotification', EventPriorities::POST_WRITE],
            ],
        ];
    }

    public function newEquipmentShippingRecordNotification(ViewEvent $event): void
    {
        $request = $event->getRequest();
        $data = $event->getControllerResult();
        if (!$data instanceof EquipmentShippingRecord || !$request->isMethod(Request::METHOD_POST)) {
            return;
        }

        $this->serviceLocator->get(EquipmentShippingRecordNotifier::class)->sendNewEquipmentShippingRecordNotification($data);
    }

    public function updateLegacyEquipmentRecord(ViewEvent $event): void
    {
        $request = $event->getRequest();
        $data = $event->getControllerResult();
        if (!$data instanceof EquipmentShippingRecord || !$request->isMethod(Request::METHOD_POST)) {
            return;
        }

        foreach ($data->getEquipmentShippingRecordLines() as $equipmentShippingRecordLine) {
            $this->serviceLocator->get(EquipmentRecordManager::class)->updateEsrIdProperty($data, $equipmentShippingRecordLine->equipmentRecord);
        }
    }

    public static function getSubscribedServices(): array
    {
        return [
            EquipmentRecordManager::class,
            EquipmentShippingRecordNotifier::class,
        ];
    }
}
