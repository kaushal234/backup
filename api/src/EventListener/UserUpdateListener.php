<?php

declare(strict_types=1);

namespace App\EventListener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Directory\People;
use App\Entity\Sales\ExtranetUser;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class UserUpdateListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function updateUsernameWhenUpdatingEmail(ViewEvent $event)
    {
        $user = $event->getControllerResult();
        $request = $event->getRequest();

        if (
            (!$user instanceof ExtranetUser && !$user instanceof People)
            || Request::METHOD_PUT !== $request->getMethod()
        ) {
            return;
        }

        $user->setUsername($user->getEmail());
    }

    public function onUpdate(ViewEvent $event)
    {
        $extranetUser = $event->getControllerResult();
        $request = $event->getRequest();

        if (!$extranetUser instanceof ExtranetUser || Request::METHOD_PUT !== $request->getMethod()) {
            return;
        }

        $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);
        $uow = $entityManager->getUnitOfWork();
        $uow->computeChangeSets();
        $changeset = $uow->getEntityChangeSet($extranetUser->getExtranetUserProfile());

        if (isset($changeset['customer'])) {
            foreach ($extranetUser->getExtranetUserAcls() as $extranetUserAcl) {
                $entityManager->remove($extranetUserAcl);
            }
        }
    }

    /** {@inheritdoc} */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['updateUsernameWhenUpdatingEmail', EventPriorities::PRE_VALIDATE],
                ['onUpdate', EventPriorities::PRE_WRITE],
            ],
        ];
    }

    public static function getSubscribedServices(): array
    {
        return [
            EntityManagerInterface::class,
        ];
    }
}
