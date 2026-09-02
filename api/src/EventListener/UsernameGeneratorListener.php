<?php

declare(strict_types=1);

namespace App\EventListener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Directory\People;
use App\Manager\Directory\PeopleManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class UsernameGeneratorListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function generateUsername(ViewEvent $event)
    {
        $user = $event->getControllerResult();

        if (!$user instanceof People || !$event->getRequest()->isMethod(Request::METHOD_POST)) {
            return;
        }
        /** @var string $username */
        $username = $user->getUserIdentifier();

        if ('' !== $username) {
            return;
        }
        $this->serviceLocator->get(PeopleManager::class)->generateUsernameAndEmail($user);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['generateUsername', EventPriorities::PRE_VALIDATE],
        ];
    }

    public static function getSubscribedServices(): array
    {
        return [
            PeopleManager::class,
        ];
    }
}
