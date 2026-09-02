<?php

declare(strict_types=1);

namespace App\Postman\EventListener;

use App\Postman\Event\PostmanAllowedMethodsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;

class AddPostMethodListener implements EventSubscriberInterface
{
    private const ROUTES = [
        '/token',
    ];

    public static function getSubscribedEvents(): array
    {
        return [
            PostmanAllowedMethodsEvent::class => 'addPostMethod',
        ];
    }

    public function addPostMethod(PostmanAllowedMethodsEvent $event): void
    {
        $route = $event->getRoute();

        if (!\in_array($route->getPath(), self::ROUTES, true)) {
            return;
        }

        $event->addMethod(Request::METHOD_POST);
    }
}
