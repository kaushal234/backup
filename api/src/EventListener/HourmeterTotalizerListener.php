<?php

declare(strict_types=1);

namespace App\EventListener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Support\EquipmentFollowUpReport;
use App\Repository\Support\EquipmentHourmeterResetRepository;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class HourmeterTotalizerListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function setTotalizer(ViewEvent $event)
    {
        $subject = $event->getControllerResult();
        if (!$subject instanceof EquipmentFollowUpReport) {
            return;
        }
        $request = $event->getRequest();
        if (!$request->isMethod(Request::METHOD_POST) && !$request->isMethod(Request::METHOD_PUT)) {
            return;
        }

        $resetedHours = $this->serviceLocator->get(EquipmentHourmeterResetRepository::class)->getResetHoursSum($subject->getEquipmentRecord(), $subject->getHourmeterDate());

        $subject->setHourmeterTotalizer($subject->getHourmeter() + $resetedHours);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['setTotalizer', EventPriorities::PRE_VALIDATE],
        ];
    }

    public static function getSubscribedServices(): array
    {
        return [
            EquipmentHourmeterResetRepository::class,
        ];
    }
}
