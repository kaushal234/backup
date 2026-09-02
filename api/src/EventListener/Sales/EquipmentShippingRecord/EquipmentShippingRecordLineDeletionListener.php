<?php

declare(strict_types=1);

namespace App\EventListener\Sales\EquipmentShippingRecord;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecord;
use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecordLine;
use LegacyBundle\Manager\CustomerServiceRecordManager;
use LegacyBundle\Manager\EquipmentRecordManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class EquipmentShippingRecordLineDeletionListener implements EventSubscriberInterface, ServiceSubscriberInterface
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
                ['validateNoCustomerServiceRecord', EventPriorities::PRE_VALIDATE],
                ['validateNotClosed', EventPriorities::PRE_VALIDATE],
                ['updateLegacyEquipmentRecord', EventPriorities::PRE_WRITE],
            ],
        ];
    }

    public function validateNotClosed(ViewEvent $event): void
    {
        $data = $event->getControllerResult();
        $request = $event->getRequest();

        if (!$data instanceof EquipmentShippingRecordLine || !$request->isMethod(Request::METHOD_DELETE)) {
            return;
        }
        if (EquipmentShippingRecord::CLOSED === $data->equipmentShippingRecord->getStatus()) {
            throw new BadRequestException('You can not delete a CLOSED ESR.');
        }
    }

    public function validateNoCustomerServiceRecord(ViewEvent $event): void
    {
        $data = $event->getControllerResult();
        $request = $event->getRequest();

        if (!$data instanceof EquipmentShippingRecordLine || !$request->isMethod(Request::METHOD_DELETE)) {
            return;
        }

        $customerServiceRecord = $this->serviceLocator->get(CustomerServiceRecordManager::class)->getCustomerServiceRecordByEquipmentRecord($data->equipmentRecord->getLegacyId());
        if (!empty($customerServiceRecord)) {
            $route = $this->serviceLocator->get(RouterInterface::class)->generate('legacy_product_support', [
                'm' => ['equipment', 'view', 'csr'],
                'id' => $data->equipmentRecord->getLegacyId(),
            ]);
            throw new BadRequestException(\sprintf('WARNING: There is at least one Commissioning CSR linked to  <a href="%s">%s</a> Please take action before unassigning this unit. <br>', $route, $this->serviceLocator->get(TranslatorInterface::class)->trans('csr.csr', ['%id%' => $data->equipmentRecord->getLegacyId()], 'emails')));
        }
    }

    public function updateLegacyEquipmentRecord(ViewEvent $event): void
    {
        $data = $event->getControllerResult();
        $request = $event->getRequest();

        if (!$data instanceof EquipmentShippingRecordLine || !$request->isMethod(Request::METHOD_DELETE)) {
            return;
        }

        $this->serviceLocator->get(EquipmentRecordManager::class)->unsetEsrIdProperty($data->equipmentShippingRecord, $data->equipmentRecord);
    }

    /**
     * @return string[]
     */
    public static function getSubscribedServices(): array
    {
        return [
            CustomerServiceRecordManager::class,
            EquipmentRecordManager::class,
            TranslatorInterface::class,
            RouterInterface::class,
        ];
    }
}
