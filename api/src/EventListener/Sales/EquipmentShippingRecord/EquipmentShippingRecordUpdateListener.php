<?php

declare(strict_types=1);

namespace App\EventListener\Sales\EquipmentShippingRecord;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecord;
use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecordLine;
use App\Manager\Sales\EquipmentShippingRecordLineManager;
use App\Notifier\Sales\CustomerServiceRecordNotifier;
use App\Notifier\Sales\EquipmentShippingRecord\EquipmentShippingRecordNotifier;
use App\Repository\Sales\EquipmentShippingRecordLineRepository;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Manager\CustomerServiceRecordManager;
use LegacyBundle\Manager\EquipmentRecordManager;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class EquipmentShippingRecordUpdateListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private array $addedEquipmentRecords = [];
    private array $deletedEquipmentRecords = [];
    private array $linesChangedForCustomerService = [];
    private array $linesChangedForPickUpInformation = [];

    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['validateNotClosed', EventPriorities::PRE_VALIDATE],
                ['allowedToClosed', EventPriorities::PRE_VALIDATE],
                ['collectInformation', EventPriorities::PRE_WRITE],
                ['onChangePickUpDateInformationChanges', EventPriorities::POST_WRITE],
                ['updateLegacyEquipmentRecord', EventPriorities::POST_WRITE],
                ['shipAuthorisationControl', EventPriorities::POST_WRITE],
                ['customerServiceRecordNotification', EventPriorities::POST_WRITE],
                ['notifyPickUpDateInformationChanges', EventPriorities::POST_WRITE],
            ],
        ];
    }

    public function onChangePickUpDateInformationChanges(ViewEvent $event): void
    {
        $request = $event->getRequest();
        $data = $event->getControllerResult();

        if (!$data instanceof EquipmentShippingRecord || !$request->isMethod(Request::METHOD_PUT)) {
            return;
        }

        if (empty($this->linesChangedForPickUpInformation)) {
            return;
        }

        $security = $this->serviceLocator->get(Security::class);

        if ($security->isGranted('FEATURE_EQUIPMENT_SHIPPING_RECORD_LINE_PICK_UP_CONFIRMATION')) {
            return;
        }

        // If feature not enabled: any change on estimatedPickUpDate => reset confirmation
        $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);

        $needFlush = false;

        foreach ($this->linesChangedForPickUpInformation as $pickUpChange) {
            if (!isset($pickUpChange['changes']['estimatedPickUpDate'])) {
                continue;
            }

            /** @var EquipmentShippingRecordLine $line */
            $line = $pickUpChange['equipmentShippingRecordLine'];

            if (false !== $line->estimatedPickUpDateConfirmation) {
                $line->estimatedPickUpDateConfirmation = false;
                $needFlush = true;
            }
        }

        if ($needFlush) {
            $entityManager->flush();
        }
    }

    public function notifyPickUpDateInformationChanges(ViewEvent $event): void
    {
        $request = $event->getRequest();
        $data = $event->getControllerResult();

        if (!$data instanceof EquipmentShippingRecord || !$request->isMethod(Request::METHOD_PUT)) {
            return;
        }

        if (empty($this->linesChangedForPickUpInformation)) {
            return;
        }

        /** @var EquipmentShippingRecordNotifier $notifier */
        $notifier = $this->serviceLocator->get(EquipmentShippingRecordNotifier::class);

        foreach ($this->linesChangedForPickUpInformation as $pickUpChange) {
            $notifier->onChangePickUpInformationNotification($pickUpChange);
        }
    }

    public function validateNotClosed(ViewEvent $event): void
    {
        $data = $event->getControllerResult();
        $request = $event->getRequest();
        if (!$data instanceof EquipmentShippingRecord || !$request->isMethod(Request::METHOD_PUT)) {
            return;
        }
        $security = $this->serviceLocator->get(Security::class);

        if (EquipmentShippingRecord::CLOSED === $request->attributes->get('previous_data')->getStatus() && !($security->isGranted('MOO_ESR') || $security->isGranted('FEATURE_EQUIPMENT_SHIPPING_RECORD_REOPEN'))) {
            throw new BadRequestException("Can't updated a CLOSED ESR and his lines");
        }
    }

    public function allowedToClosed(ViewEvent $event): void
    {
        $data = $event->getControllerResult();
        $request = $event->getRequest();

        /** @var Operation $operation */
        $operation = $request->attributes->get('_api_operation');
        if ($data instanceof EquipmentShippingRecord
            && $request->isMethod(Request::METHOD_PUT)
            && 'update_equipment_shipping_record_status' === $operation->getName()
            && EquipmentShippingRecord::CLOSED === $data->getStatus()
        ) {
            foreach ($data->getEquipmentShippingRecordLines() as $line) {
                if (null === $line->actualArrivalDate) {
                    throw new BadRequestException(\sprintf('INTERNAL ERROR: Status not updated to CLOSED. Reason: ESRL# %s Actual Arrival Date not set', $line->getId()));
                }
            }
        }
    }

    public function collectInformation(ViewEvent $event)
    {
        $request = $event->getRequest();
        $data = $event->getControllerResult();

        if (!$data instanceof EquipmentShippingRecord || !$request->isMethod(Request::METHOD_PUT)) {
            return;
        }

        $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);
        $uow = $entityManager->getUnitOfWork();
        $uow->computeChangeSets();

        $newCollection = $data->getEquipmentShippingRecordLines();

        if (\array_key_exists(EquipmentShippingRecordLine::class, $uow->getIdentityMap())) {
            $oldCollection = $uow->getIdentityMap()[EquipmentShippingRecordLine::class];
            foreach ($oldCollection as $oldItem) {
                if (!$newCollection->contains($oldItem)) {
                    $this->deletedEquipmentRecords[] = $oldItem->equipmentRecord;
                }
            }
        }

        foreach ($data->getEquipmentShippingRecordLines() as $equipmentShippingRecordLine) {
            $changeSet = $uow->getEntityChangeSet($equipmentShippingRecordLine);
            if (\array_key_exists('equipmentRecord', $changeSet)) {
                [$oldEquipmentRecord, $newEquipmentRecord] = $changeSet['equipmentRecord'];
                if (null !== $oldEquipmentRecord) {
                    $this->deletedEquipmentRecords[] = $oldEquipmentRecord;
                }
                $this->addedEquipmentRecords[] = $newEquipmentRecord;
            }
            $actualArrivalDateChanged = \array_key_exists('actualArrivalDate', $changeSet)
                && $changeSet['actualArrivalDate'][0]?->format('Y-m-d') !== $equipmentShippingRecordLine->actualArrivalDate?->format('Y-m-d');
            $estimatedArrivalDateChanged = \array_key_exists('estimatedArrivalDate', $changeSet)
                && $changeSet['estimatedArrivalDate'][0]?->format('Y-m-d') !== $equipmentShippingRecordLine->estimatedArrivalDate?->format('Y-m-d');

            if ($actualArrivalDateChanged || $estimatedArrivalDateChanged) {
                $this->linesChangedForCustomerService[] = [
                    'equipmentShippingRecordLine' => $equipmentShippingRecordLine,
                    'newDate' => $actualArrivalDateChanged ? $equipmentShippingRecordLine->actualArrivalDate?->format('Y-m-d') : $equipmentShippingRecordLine->estimatedArrivalDate?->format('Y-m-d'),
                ];
            }

            /*
             * If estimatedPickUpDateConfirmation or estimatedPickUpDate not present in the change set, the value is considered unchanged.
             */
            if (\array_key_exists('estimatedPickUpDateConfirmation', $changeSet)) {
                [$oldConfirmation, $newConfirmation] = $changeSet['estimatedPickUpDateConfirmation'];
            } else {
                $newConfirmation = $equipmentShippingRecordLine->estimatedPickUpDateConfirmation;
                $oldConfirmation = $newConfirmation;
            }

            $newEstimatedPickUpDate = $equipmentShippingRecordLine->estimatedPickUpDate;
            $oldEstimatedPickUpDate = $newEstimatedPickUpDate;

            if (\array_key_exists('estimatedPickUpDate', $changeSet)) {
                [$oldEstimatedPickUpDate, $newEstimatedPickUpDate] = $changeSet['estimatedPickUpDate'];
            }

            $pickUpChanges = $this->serviceLocator->get(EquipmentShippingRecordLineManager::class)->getPickUpInformationChanges(
                $oldEstimatedPickUpDate,
                $newEstimatedPickUpDate,
                $oldConfirmation,
                $newConfirmation
            );
            if (!empty($pickUpChanges)) {
                $key = $equipmentShippingRecordLine->equipmentRecord->getSerialNumber();

                $this->linesChangedForPickUpInformation[$key] = [
                    'equipmentShippingRecordLine' => $equipmentShippingRecordLine,
                    'changes' => $pickUpChanges,
                ];
            }
        }
    }

    public function updateLegacyEquipmentRecord(ViewEvent $event): void
    {
        $request = $event->getRequest();
        $data = $event->getControllerResult();

        if (!$data instanceof EquipmentShippingRecord || !$request->isMethod(Request::METHOD_PUT)) {
            return;
        }

        foreach ($this->deletedEquipmentRecords as $equipmentRecord) {
            $this->serviceLocator->get(EquipmentRecordManager::class)->unsetEsrIdProperty($data, $equipmentRecord);
        }

        foreach ($this->addedEquipmentRecords as $equipmentRecord) {
            $this->serviceLocator->get(EquipmentRecordManager::class)->updateEsrIdProperty($data, $equipmentRecord);
        }
    }

    public function shipAuthorisationControl(ViewEvent $event): void
    {
        $request = $event->getRequest();
        $data = $event->getControllerResult();

        if (!$data instanceof EquipmentShippingRecord || !$request->isMethod(Request::METHOD_PUT)) {
            return;
        }

        if ((!empty($this->deletedEquipmentRecords) || !empty($this->addedEquipmentRecords)) && $data->haveToChangeShipAuthorization()) {
            $data->shipAuthorization = false;
            $this->serviceLocator->get(EntityManagerInterface::class)->flush();
        }
    }

    public function customerServiceRecordNotification(ViewEvent $event): void
    {
        $request = $event->getRequest();
        $data = $event->getControllerResult();

        if (!$data instanceof EquipmentShippingRecord || !$request->isMethod(Request::METHOD_PUT)) {
            return;
        }

        if (!empty($this->linesChangedForCustomerService)) {
            $customerServiceRecordManager = $this->serviceLocator->get(CustomerServiceRecordManager::class);

            foreach ($this->linesChangedForCustomerService as $line) {
                $customerServiceRecords = $customerServiceRecordManager->getCustomerServiceRecordByEquipmentRecord($line['equipmentShippingRecordLine']->equipmentRecord->getLegacyId());
                if (1 === \count($customerServiceRecords)) {
                    $customerServiceRecordManager->updateDateSchedule($customerServiceRecords[0]['id'], $line['newDate']);
                    $this->serviceLocator->get(CustomerServiceRecordNotifier::class)->sendTechnicianEmail($customerServiceRecords[0], $line['newDate']);
                }
            }
        }
    }

    /**
     * @return string[]
     */
    public static function getSubscribedServices(): array
    {
        return [
            CustomerServiceRecordManager::class,
            EntityManagerInterface::class,
            EquipmentRecordManager::class,
            CustomerServiceRecordNotifier::class,
            EquipmentShippingRecordNotifier::class,
            Security::class,
            EquipmentShippingRecordLineRepository::class,
            EquipmentShippingRecordLineManager::class,
        ];
    }
}
