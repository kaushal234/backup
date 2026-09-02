<?php

declare(strict_types=1);

namespace App\EventListener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Directory\People;
use App\Entity\Purchasing\VendorUser;
use App\Entity\Sales\ExtranetUser;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class UserLinkedAccountListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['linkAccount', EventPriorities::PRE_WRITE],
        ];
    }

    public function linkAccount(ViewEvent $event)
    {
        $user = $event->getControllerResult();

        if (!$user instanceof People && !$user instanceof ExtranetUser) {
            return;
        }

        if (!$event->getRequest()->isMethod(Request::METHOD_POST)) {
            return;
        }

        $registry = $this->serviceLocator->get(ManagerRegistry::class);
        $manager = $registry->getManager();

        $classes = $user instanceof People
            ? [ExtranetUser::class, VendorUser::class]
            : [People::class, VendorUser::class];

        foreach ($classes as $class) {
            if (null === ($userFound = $registry->getRepository($class)->findOneBy(['username' => $user->getUserIdentifier()]))) {
                continue;
            }

            switch (true) {
                case $userFound instanceof ExtranetUser && $user instanceof People:
                    $user->setExtranetUserLinked($userFound);
                    break;
                case $userFound instanceof VendorUser && $user instanceof People:
                case $userFound instanceof VendorUser && $user instanceof ExtranetUser:
                    $user->setVendorUserLinked($userFound);
                    break;
                case $userFound instanceof People && $user instanceof ExtranetUser:
                    $userFound->setExtranetUserLinked($user);
                    $manager->persist($userFound);
                    break;
            }
        }
    }

    public static function getSubscribedServices(): array
    {
        return [
            ManagerRegistry::class,
        ];
    }
}
