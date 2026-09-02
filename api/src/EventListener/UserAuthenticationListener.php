<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Timestampable\TimestampableListener;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class UserAuthenticationListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $user = $event->getUser();

        if (!$user instanceof User) {
            return;
        }

        $em = $this->serviceLocator->get(EntityManagerInterface::class);
        $eventManager = $em->getEventManager();
        /** @var array $eventManagerListeners */
        $eventManagerListeners = $eventManager->getAllListeners();
        $timestampableListener = null;
        foreach ($eventManagerListeners as $listeners) {
            foreach ($listeners as $hash => $listener) {
                if ($listener instanceof TimestampableListener) {
                    $eventManager->removeEventListener($listener->getSubscribedEvents(), $listener);
                    $timestampableListener = $listener;
                }
            }
        }

        $user->setLastLogin(new \DateTime());

        $em->persist($user);
        $em->flush();

        if (null !== $timestampableListener) {
            $eventManager->addEventListener($timestampableListener->getSubscribedEvents(), $timestampableListener);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            Events::JWT_CREATED => 'onJWTCreated',
        ];
    }

    public static function getSubscribedServices(): array
    {
        return [
            EntityManagerInterface::class,
        ];
    }
}
