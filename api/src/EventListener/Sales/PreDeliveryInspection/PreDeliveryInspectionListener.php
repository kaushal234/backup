<?php

declare(strict_types=1);

namespace App\EventListener\Sales\PreDeliveryInspection;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\AbstractInspection;
use App\Entity\Sales\OrderLine;
use App\Entity\Sales\PreDeliveryInspection;
use App\Notifier\Sales\PreDeliveryInspection\PreDeliveryInspectionNotifier;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PreDeliveryInspectionListener implements EventSubscriberInterface, ServiceSubscriberInterface
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
                ['onTimeDeliveryPlanningPostCreate', EventPriorities::POST_WRITE],
                ['onTimeDeliveryPlanningPostDateUpdate', EventPriorities::POST_WRITE],
                ['onTimeDeliveryStatusEnded', EventPriorities::POST_WRITE],
            ],
        ];
    }

    public static function getSubscribedServices(): array
    {
        return [
            EntityManagerInterface::class,
            Security::class,
            TranslatorInterface::class,
            IriConverterInterface::class,
            PreDeliveryInspectionNotifier::class,
        ];
    }

    public function onTimeDeliveryPlanningPostCreate(ViewEvent $event): void
    {
        $route = $event->getRequest()->attributes->get('_route');

        $preDeliveryInspection = $event->getControllerResult();
        if ('pre_delivery_inspection_create' !== $route || !$preDeliveryInspection instanceof PreDeliveryInspection) {
            return;
        }

        $entityManager = $this->container->get(EntityManagerInterface::class);

        if (
            $preDeliveryInspection->getEquipmentRecord()->getPreDeliveryInspections()->isEmpty()
            && !$preDeliveryInspection->getEquipmentRecord()->orderFactory?->orderLine->inspection
        ) {
            $orderLine = $entityManager->getRepository(OrderLine::class)->find($preDeliveryInspection->getEquipmentRecord()->orderFactory->orderLine->getId());
            $orderLine->inspection = true;
            $entityManager->persist($orderLine);
            $entityManager->flush();
        }

        if (null !== $preDeliveryInspection->getPlannedAt()) {
            $this->container->get(PreDeliveryInspectionNotifier::class)->sendNewPdiScheduled($preDeliveryInspection);
        }
    }

    public function onTimeDeliveryPlanningPostDateUpdate(ViewEvent $event): void
    {
        $route = $event->getRequest()->attributes->get('_route');

        $preDeliveryInspection = $event->getControllerResult();
        if ('pre_delivery_inspection_edit' !== $route || !$preDeliveryInspection instanceof PreDeliveryInspection) {
            return;
        }

        /** @var PreDeliveryInspection $previousPreDeliveryInspection */
        $previousPreDeliveryInspection = $event->getRequest()->attributes->get('previous_data');

        if (null === $previousPreDeliveryInspection->getPlannedAt() && null !== $preDeliveryInspection->getPlannedAt()) {
            $this->container->get(PreDeliveryInspectionNotifier::class)->sendNewPdiScheduled($preDeliveryInspection);
        }

        if (
            $previousPreDeliveryInspection->getPlannedAt()->format('Y-m-d') !== $preDeliveryInspection->getPlannedAt()->format('Y-m-d')
            && null !== $previousPreDeliveryInspection->getPlannedAt()
            && null !== $preDeliveryInspection->getPlannedAt()
        ) {
            $this->container->get(PreDeliveryInspectionNotifier::class)
                ->sendPdiDateChanged($preDeliveryInspection, $previousPreDeliveryInspection->getPlannedAt()->format('Y-m-d'));
        }
    }

    public function onTimeDeliveryStatusEnded(ViewEvent $event): void
    {
        $route = $event->getRequest()->attributes->get('_route');

        $preDeliveryInspection = $event->getControllerResult();
        if ('update_pre_delivery_inspection_status' !== $route || !$preDeliveryInspection instanceof PreDeliveryInspection) {
            return;
        }

        /** @var PreDeliveryInspection $previousPreDeliveryInspection */
        $previousPreDeliveryInspection = $event->getRequest()->attributes->get('previous_data');

        if (
            $preDeliveryInspection->getStatus() !== $previousPreDeliveryInspection->getStatus()
            && \in_array($preDeliveryInspection->getStatus(), AbstractInspection::CLOSED_STATUSES, true)
        ) {
            $this->container->get(PreDeliveryInspectionNotifier::class)
                ->sendPdiClosed($preDeliveryInspection);
        }
    }
}
