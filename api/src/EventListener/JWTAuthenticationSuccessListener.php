<?php

declare(strict_types=1);

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTAuthenticatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class JWTAuthenticationSuccessListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    final public const RAW_JWT_ATTRIBUTE = '_raw_jwt_payload';

    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function onAuthenticationSuccess(JWTAuthenticatedEvent $event)
    {
        if (null === $request = $this->serviceLocator->get(RequestStack::class)->getCurrentRequest()) {
            return;
        }
        $request->attributes->set(self::RAW_JWT_ATTRIBUTE, $event->getPayload());
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return [
            RequestStack::class,
        ];
    }

    public static function getSubscribedEvents(): array
    {
        return [Events::JWT_AUTHENTICATED => 'onAuthenticationSuccess'];
    }
}
