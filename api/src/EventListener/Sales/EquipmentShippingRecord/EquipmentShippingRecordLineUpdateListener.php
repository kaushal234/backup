<?php

declare(strict_types=1);

namespace App\EventListener\Sales\EquipmentShippingRecord;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecordLine;
use App\Manager\Sales\EquipmentShippingRecordLineManager;
use App\Notifier\Sales\EquipmentShippingRecord\EquipmentShippingRecordNotifier;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class EquipmentShippingRecordLineUpdateListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    public function __construct(private readonly ContainerInterface $serviceLocator)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['notifyPickUpDateInformationChanges', EventPriorities::POST_WRITE],
            ],
        ];
    }

    public function notifyPickUpDateInformationChanges(ViewEvent $event): void
    {
        $request = $event->getRequest();
        $data = $event->getControllerResult();

        if (!$data instanceof EquipmentShippingRecordLine || !$request->isMethod(Request::METHOD_PUT)) {
            return;
        }

        /** @var EquipmentShippingRecordLine|null $previous */
        $previous = $request->attributes->get('previous_data');
        if (!$previous instanceof EquipmentShippingRecordLine) {
            return;
        }

        $oldDate = $previous->estimatedPickUpDate;
        $newDate = $data->estimatedPickUpDate;

        $oldConfirmation = $previous->estimatedPickUpDateConfirmation;
        $newConfirmation = $data->estimatedPickUpDateConfirmation;

        $pickUpChanges = $this->serviceLocator->get(EquipmentShippingRecordLineManager::class)->getPickUpInformationChanges(
            $oldDate,
            $newDate,
            $oldConfirmation,
            $newConfirmation
        );

        if (!empty($pickUpChanges)) {
            $this->serviceLocator->get(EquipmentShippingRecordNotifier::class)->onChangePickUpInformationNotification([
                'equipmentShippingRecordLine' => $data,
                'changes' => $pickUpChanges,
            ]);
        }
    }

    /**
     * @return string[]
     */
    public static function getSubscribedServices(): array
    {
        return [
            EquipmentShippingRecordNotifier::class,
            EquipmentShippingRecordLineManager::class,
        ];
    }
}
