<?php

declare(strict_types=1);

namespace App\EventListener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\AuthorizedApplication;
use App\Manager\UserManager;
use App\Security\JWT\JWTEncoder;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class AuthorizedApplicationKeyListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['onAPIClientKeyCreation', EventPriorities::POST_WRITE],
            ],
        ];
    }

    public function onAPIClientKeyCreation(ViewEvent $event): void
    {
        $authorizedApplication = $event->getControllerResult();
        $request = $event->getRequest();

        if (!$authorizedApplication instanceof AuthorizedApplication || !$request->isMethod(Request::METHOD_POST)) {
            return;
        }

        /** @var JWTEncoder $jwtEncoder */
        $jwtEncoder = $this->serviceLocator->get(JWTEncoder::class);
        $authorizedApplication->key = $jwtEncoder->encode([JWTEncoder::PAYLOAD_USER_KEY => $authorizedApplication, UserManager::JWT_PROPERTY_USERNAME => $authorizedApplication->getUserIdentifier()]);
    }

    public static function getSubscribedServices(): array
    {
        return [
            JWTEncoder::class,
        ];
    }
}
