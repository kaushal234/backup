<?php

declare(strict_types=1);

namespace App\EventListener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Directory\Position;
use App\Entity\MIS\GuestUser\GuestUser;
use App\Repository\Directory\PositionRepository;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class GuestUserListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    public function __construct(
        private readonly ContainerInterface $container
    ) {
    }

    public static function getSubscribedServices(): array
    {
        return [
            PositionRepository::class,
        ];
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['preValidate', EventPriorities::PRE_VALIDATE],
            ],
        ];
    }

    public function preValidate(ViewEvent $event): void
    {
        $guestUser = $event->getControllerResult();

        if (!$guestUser instanceof GuestUser) {
            return;
        }

        $guestUser->setPosition($this->container->get(PositionRepository::class)->findOneBy(['code' => Position::GUEST]));
    }
}
