<?php

declare(strict_types=1);

namespace App\EventListener\Sales\ExtranetUser;

use App\Entity\Sales\ExtranetUser;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class ExtranetUserAuthenticationListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $extranetUser = $event->getUser();

        if (!$extranetUser instanceof ExtranetUser) {
            return;
        }

        $requestStack = $this->serviceLocator->get(RequestStack::class);
        $request = $requestStack->getCurrentRequest();
        $requestStack->pop();

        $extranetUserProfile = $extranetUser->getExtranetUserProfile();
        $extranetUserProfile->incrementCounter();

        $em = $this->serviceLocator->get(EntityManagerInterface::class);
        $em->persist($extranetUserProfile);
        $em->flush();

        if (null !== $request) {
            $requestStack->push($request);
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

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return [
            EntityManagerInterface::class,
            RequestStack::class,
        ];
    }
}
