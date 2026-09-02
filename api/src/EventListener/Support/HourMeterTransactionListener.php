<?php

declare(strict_types=1);

namespace App\EventListener\Support;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Support\EquipmentRecord\HourMeterTransaction\HourMeterTransaction;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class HourMeterTransactionListener implements EventSubscriberInterface, ServiceSubscriberInterface
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
                ['onPreWrite', EventPriorities::PRE_WRITE],
            ],
        ];
    }

    public static function getSubscribedServices(): array
    {
        return [
            EntityManagerInterface::class,
        ];
    }

    public function onPreWrite(ViewEvent $event)
    {
        $hourMeterTransaction = $event->getControllerResult();
        $request = $event->getRequest();

        if (!$hourMeterTransaction instanceof HourMeterTransaction || !\in_array($request->getMethod(), [Request::METHOD_POST, Request::METHOD_PUT], true)) {
            return;
        }

        /** @var EntityManagerInterface$entityManager */
        $entityManager = $this->container->get(EntityManagerInterface::class);

        $hourMeterTransaction->getEquipmentRecord()->setHourMeter($hourMeterTransaction->hourMeter);
        $entityManager->persist($hourMeterTransaction->getEquipmentRecord());
    }
}
