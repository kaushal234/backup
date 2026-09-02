<?php

declare(strict_types=1);

namespace App\EventListener;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Activity\Comment;
use App\Entity\User;
use App\Manager\UserManager;
use App\Notifier\User\UserPasswordNotifier;
use Cake\Chronos\Chronos;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class UserPasswordListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function generatePassword(ViewEvent $event)
    {
        if (!$event->getRequest()->isMethod(Request::METHOD_POST)) {
            return;
        }

        $user = $event->getControllerResult();
        if (!$user instanceof User) {
            return;
        }

        if (!empty($user->getClearPassword())) {
            $this->serviceLocator->get(UserManager::class)->encodePassword($user);

            return;
        }

        $this->serviceLocator->get(UserManager::class)->generatePassword($user);
    }

    public function encodePassword(ViewEvent $event)
    {
        if (!$event->getRequest()->isMethod(Request::METHOD_PUT)) {
            return;
        }

        $user = $event->getControllerResult();
        if (!$user instanceof User) {
            return;
        }

        $this->serviceLocator->get(UserManager::class)->encodePassword($user);
    }

    public function logPassword(ViewEvent $event)
    {
        if (!$event->getRequest()->isMethod(Request::METHOD_PUT)) {
            return;
        }

        $user = $event->getControllerResult();
        if (!$user instanceof User) {
            return;
        }

        if (empty($user->getClearPassword())) {
            return;
        }

        $this->serviceLocator->get(UserManager::class)->logPassword($user);
        $this->serviceLocator->get(UserPasswordNotifier::class)->sendPasswordChanged($user);

        if (null === $authenticatedUser = $this->serviceLocator->get(Security::class)->getUser()) {
            return;
        }

        $comment = (new Comment())
            ->setMessage('updated user password')
            ->setResource($this->serviceLocator->get(IriConverterInterface::class)->getIriFromResource($user))
            ->setCreatedAt(new \DateTime(Chronos::now()->toDateString()))
            ->setUser($authenticatedUser)
        ;

        $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);
        $entityManager->persist($comment);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['generatePassword', EventPriorities::PRE_VALIDATE],
                ['encodePassword', EventPriorities::PRE_VALIDATE],
                ['logPassword', EventPriorities::POST_VALIDATE],
            ],
        ];
    }

    public static function getSubscribedServices(): array
    {
        return [
            UserManager::class,
            EntityManagerInterface::class,
            IriConverterInterface::class,
            Security::class,
            UserPasswordNotifier::class,
        ];
    }
}
