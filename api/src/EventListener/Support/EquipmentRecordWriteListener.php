<?php

declare(strict_types=1);

namespace App\EventListener\Support;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Directory\People;
use App\Entity\EquipmentRecord;
use App\Entity\Quality\Crab;
use App\Entity\Sales\OrderLine;
use App\Entity\Support\Component;
use App\Entity\Support\EquipmentSerial;
use App\Manager\EquipmentRecordManager;
use App\Manager\Sales\EquipmentShippingRecordManager;
use App\Message\Support\EquipmentRecordGreenTagUpdate;
use App\MessageHandler\Support\EquipmentRecordGreenTagUpdateHandler;
use App\Notifier\Support\EquipmentRecord\EquipmentRecordNotifier;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Manager\ModLogManager;
use LegacyBundle\Manager\SequenceManager;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class EquipmentRecordWriteListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['preWrite', EventPriorities::PRE_WRITE],
                ['equipmentRecordOdpPreWrite', EventPriorities::PRE_WRITE],
                ['equipmentRecordOdpPostWrite', EventPriorities::POST_WRITE],
            ],
        ];
    }

    public static function getSubscribedServices(): array
    {
        return [
            EntityManagerInterface::class,
            MessageBusInterface::class,
            Security::class,
            EquipmentRecordNotifier::class,
            SequenceManager::class,
            TranslatorInterface::class,
            IriConverterInterface::class,
            EquipmentRecordManager::class,
            EquipmentShippingRecordManager::class,
            ModLogManager::class,
        ];
    }

    public function preWrite(ViewEvent $event)
    {
        $equipmentRecord = $event->getControllerResult();
        $request = $event->getRequest();

        if (!$equipmentRecord instanceof EquipmentRecord || !$request->isMethod(Request::METHOD_PUT)) {
            return;
        }

        /** @var EntityManagerInterface$entityManager */
        $entityManager = $this->container->get(EntityManagerInterface::class);
        $serialRepository = $entityManager->getRepository(EquipmentSerial::class);

        $originalManualSerials = $serialRepository->findByComponentNameAndEquipmentRecord($equipmentRecord, Component::MANUAL);

        $currentManualSerials = [];
        $originalsManualSerialsIds = array_column($originalManualSerials, 'id');

        // Refresh the data of serials type of MANUAL. User cannot modify it.
        foreach ($equipmentRecord->getSerials() as $serial) {
            // Test if it's a new element
            if (!$entityManager->contains($serial) || !\in_array((string) $serial->getId(), $originalsManualSerialsIds, true)) {
                // This is a new MANUAL serial. Users cannot create on PUT, only manual generation can do it
                if (Component::MANUAL === $serial->component->name) {
                    $equipmentRecord->getSerials()->removeElement($serial);
                }

                continue;
            }

            $entityManager->refresh($serial);

            // Cached the MANUAL serial id present on PUT method for prevent of null getId
            $currentManualSerials[] = $serial->getId();
        }

        // Reset deleted serial of type Manual. User cannot delete serial of type MANUAL with PUT method.
        foreach ($originalManualSerials as $originalManualSerial) {
            if (\in_array((int) $originalManualSerial['id'], $currentManualSerials, true)) {
                continue;
            }

            $serial = $serialRepository->find((int) $originalManualSerial['id']);
            $equipmentRecord->addSerial($serial);
        }
    }

    public function equipmentRecordOdpPreWrite(ViewEvent $event): void
    {
        $route = $event->getRequest()->attributes->get('_route');
        $equipmentRecord = $event->getControllerResult();
        if ('odp_er_edit' !== $route || !$equipmentRecord instanceof EquipmentRecord) {
            return;
        }
        $security = $this->container->get(Security::class);
        $sequenceManager = $this->container->get(SequenceManager::class);
        /** @var TranslatorInterface $translator */
        $translator = $this->container->get(TranslatorInterface::class);
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->container->get(EntityManagerInterface::class);
        $isFromSupportTeam = $security->isGranted('FEATURE_ODP_EDIT_SUPPORT');
        $isFromQualityTeam = $security->isGranted('FEATURE_ODP_EDIT_QUALITY');
        /** @var EquipmentRecord $previousEquipmentRecord */
        $previousEquipmentRecord = $event->getRequest()->attributes->get('previous_data');

        if ($security->isGranted('FEATURE_ODP_EDIT_ADMIN') || $security->isGranted('MOO_ER')) {
            return;
        }

        if (
            ($equipmentRecord->getDateShipped()?->getTimestamp() !== $previousEquipmentRecord->getDateShipped()?->getTimestamp() && !$isFromSupportTeam)
            || ($previousEquipmentRecord->getEstimatedGreenTagDate()?->getTimeStamp() !== $equipmentRecord->getEstimatedGreenTagDate()?->getTimeStamp() && !$security->isGranted('GG_SUPPORT', $equipmentRecord->getManufacturerLocation()))
            || (($previousEquipmentRecord->getYellowTagDate()?->getTimeStamp() !== $equipmentRecord->getYellowTagDate()?->getTimestamp()) && !$isFromQualityTeam)
            || (($previousEquipmentRecord->getGreenTagDate()?->getTimeStamp() !== $equipmentRecord->getGreenTagDate()?->getTimestamp()) && !$isFromQualityTeam)
            || (($previousEquipmentRecord->getOdpComment() !== $equipmentRecord->getOdpComment()) && !$isFromSupportTeam)
        ) {
            throw new AccessDeniedException();
        }

        if (
            null !== $equipmentRecord->getDateShipped()
            && (null === $equipmentRecord->getGreenTagDate() || ($equipmentRecord->getYellowTagDate() ?? null) > $equipmentRecord->getGreenTagDate())
            && $previousEquipmentRecord->getDateShipped()?->getTimestamp() !== $equipmentRecord->getDateShipped()->getTimestamp()
        ) {
            throw new BadRequestException($translator->trans('support.equipment_record.odp_edit_errors.date_shipped_without_green_tag', ['%legacyId%' => $equipmentRecord->getLegacyId()], 'support'));
        }

        if (
            null !== $equipmentRecord->getGreenTagDate()
            && $previousEquipmentRecord->getGreenTagDate()?->getTimestamp() !== $equipmentRecord->getGreenTagDate()->getTimestamp()
            && null === $equipmentRecord->getEstimatedGreenTagDate()
        ) {
            throw new BadRequestException($translator->trans('support.equipment_record.odp_edit_errors.green_tag_without_estimated', ['%legacyId%' => $equipmentRecord->getLegacyId()], 'support'));
        }

        $crabRepository = $entityManager->getRepository(Crab::class);
        $openCrabs = $crabRepository->findOpenCrabByEquipmentRecord($equipmentRecord);
        if (null !== $equipmentRecord->getGreenTagDate() && 0 < \count($openCrabs)) {
            throw new BadRequestException($translator->trans('support.equipment_record.odp_edit_errors.green_tag_with_crabs', ['%legacyId%' => $equipmentRecord->getLegacyId(), '%crabsCount%' => \count($openCrabs)], 'support'));
        }

        if (
            null !== $equipmentRecord->getGreenTagDate()
            && $previousEquipmentRecord->getGreenTagDate()?->getTimestamp() !== $equipmentRecord->getGreenTagDate()->getTimestamp()
            && null === $equipmentRecord->orderFactory
            && !$equipmentRecord->isLight()
        ) {
            throw new BadRequestException($translator->trans('support.equipment_record.odp_edit_errors.green_tag_without_order', [], 'support'));
        }

        if (
            null !== $equipmentRecord->getGreenTagDate()
            && $previousEquipmentRecord->getGreenTagDate()?->getTimestamp() !== $equipmentRecord->getGreenTagDate()->getTimestamp()
            && ($equipmentRecord->orderFactory && !\in_array($equipmentRecord->orderFactory->orderLine->status, [OrderLine::SHIPPED, OrderLine::IN_PROGRESS], true))
        ) {
            throw new BadRequestException($translator->trans('support.equipment_record.odp_edit_errors.green_tag_with_wrong_order_line_status', ['%legacyId%' => $equipmentRecord->getLegacyId(), '%orderLineStatus%' => $equipmentRecord->orderFactory->orderLine->status], 'support'));
        }

        if (
            null !== $equipmentRecord->getGreenTagDate()
            && $previousEquipmentRecord->getGreenTagDate()?->getTimestamp() !== $equipmentRecord->getGreenTagDate()->getTimestamp()
            && (
                $sequenceManager->findOpenSequence(EquipmentRecordGreenTagUpdateHandler::SEQUENCE_LAST_DAY, $equipmentRecord->getLegacyId())
                || $sequenceManager->findOpenSequence(EquipmentRecordGreenTagUpdateHandler::SEQUENCE_LAST_DAY_1, $equipmentRecord->getLegacyId())
                || $sequenceManager->findOpenSequence(EquipmentRecordGreenTagUpdateHandler::SEQUENCE_LAST_DAY_2, $equipmentRecord->getLegacyId())
            )
        ) {
            throw new BadRequestException($translator->trans('support.equipment_record.odp_edit_errors.green_tag_with_late_sequence', ['%legacyId%' => $equipmentRecord->getLegacyId()], 'support'));
        }

        if ($equipmentRecord->getYellowTagDate() > $equipmentRecord->getGreenTagDate()) {
            $equipmentRecord->setGreenTagDate(null);
            $this->container->get(ModLogManager::class)->insertLog($equipmentRecord->getLegacyId(), 'ER', 'Update from ODP');
        }
    }

    public function equipmentRecordOdpPostWrite(ViewEvent $event): void
    {
        $result = $event->getControllerResult();
        $previousEquipmentRecord = $event->getRequest()->attributes->get('previous_data');

        if (
            !$result instanceof EquipmentRecord
            || !$previousEquipmentRecord instanceof EquipmentRecord
            || !$event->getRequest()->isMethod(Request::METHOD_PUT)
        ) {
            return;
        }

        $previousShippedDate = null === ($previousEquipmentRecord->getDateShipped() ?? null) ? null : $previousEquipmentRecord->getDateShipped()->format('Y-m-d');
        // If a Ship date is set on the ER, run the function that changes the ESR status when needed
        if (null !== $result->getDateShipped() && $previousShippedDate !== $result->getDateShipped()->format('Y-m-d')) {
            $this->container->get(EquipmentShippingRecordManager::class)->processDateShippedChangedForEquipmentShippingRecord($result, $previousShippedDate);
        }

        /** @var People $user */
        $user = $this->container->get(Security::class)->getUser();

        $previousGreenTagDate = null === ($previousEquipmentRecord->getGreenTagDate() ?? null) ? null : $previousEquipmentRecord->getGreenTagDate()->format('Y-m-d');

        if (null !== $result->getGreenTagDate() && $previousGreenTagDate !== $result->getGreenTagDate()->format('Y-m-d')) {
            $userIri = $this->container->get(IriConverterInterface::class)->getIriFromResource($user);
            $equipmentRecordIri = $this->container->get(IriConverterInterface::class)->getIriFromResource($result);
            $this->container->get(MessageBusInterface::class)->dispatch(new EquipmentRecordGreenTagUpdate($equipmentRecordIri, $previousGreenTagDate, $userIri));
        }

        $previousEstimatedGreenTagDate = null === ($previousEquipmentRecord->getEstimatedGreenTagDate() ?? null) ? null : $previousEquipmentRecord->getEstimatedGreenTagDate()->format('Y-m-d');
        if (
            null !== $result->getEstimatedGreenTagDate()
            && $previousEstimatedGreenTagDate !== $result->getEstimatedGreenTagDate()->format('Y-m-d')
            && new \DateTime('+ 8 days') > $result->getEstimatedGreenTagDate()
        ) {
            $this->container->get(EquipmentRecordNotifier::class)->sendEstimatedGreenTagDateEmail($result, $previousEstimatedGreenTagDate);
        }

        if ($this->container->get(EquipmentRecordManager::class)->updatedEstimatedGreenTagDateNeedsToSendGapAlert($result, $previousEquipmentRecord->getEstimatedGreenTagDate())) {
            $this->container->get(EquipmentRecordNotifier::class)->sendAlertEstimatedGreenTagDateGap($result, $previousEstimatedGreenTagDate);
        }

        if (null !== $result->getFirstGreenTagDate() && null === $previousEquipmentRecord->getFirstGreenTagDate()) {
            $this->container->get(EquipmentRecordManager::class)->setLastCBOMUpdateDate($result);
        }

        if (null === $previousEstimatedGreenTagDate || null === $result->getEstimatedGreenTagDate() || null !== $result->getFirstGreenTagDate()) {
            return;
        }

        $previousYellowTagDate = null === ($previousEquipmentRecord->getYellowTagDate() ?? null) ? null : $previousEquipmentRecord->getYellowTagDate()->format('Y-m-d');

        if (null !== $result->getYellowTagDate() && $previousYellowTagDate !== $result->getYellowTagDate()->format('Y-m-d')) {
            $this->container->get(EquipmentRecordNotifier::class)->sendYellowTagDateEmail($result, $previousYellowTagDate);
        }
    }
}
