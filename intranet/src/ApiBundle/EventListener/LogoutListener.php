<?php

declare(strict_types=1);

namespace ApiBundle\EventListener;

use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\Security\Http\HttpUtils;

class LogoutListener
{
    public function __construct(private readonly HttpUtils $httpUtils)
    {
    }

    public function onLogout(LogoutEvent $event)
    {
        $response = $this->httpUtils->createRedirectResponse($event->getRequest(), '/');

        $event->setResponse($response);
    }
}
