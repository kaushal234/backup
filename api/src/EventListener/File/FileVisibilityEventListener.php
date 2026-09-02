<?php

declare(strict_types=1);

namespace App\EventListener\File;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Directory\People;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class FileVisibilityEventListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        if (null === $user = $this->container->get(Security::class)->getUser()) {
            return;
        }

        if ($user instanceof People) {
            return;
        }

        $this->container->get(EntityManagerInterface::class)
            ->getFilters()
            ->enable('public_files')
        ;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', EventPriorities::PRE_READ]],
        ];
    }

    public static function getSubscribedServices(): array
    {
        return [
            Security::class,
            EntityManagerInterface::class,
        ];
    }
}
