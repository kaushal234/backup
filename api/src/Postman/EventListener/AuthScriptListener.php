<?php

declare(strict_types=1);

namespace App\Postman\EventListener;

use App\Postman\Event\NodeEvent;
use App\Postman\Factory\EventFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AuthScriptListener implements EventSubscriberInterface
{
    private const ROUTES = [
        '/token',
        '/user-tokens/{id}',
        '/user-tokens',
    ];

    public function __construct(
        private readonly EventFactory $eventFactory,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            NodeEvent::class => 'addAuthScript',
        ];
    }

    public function addAuthScript(NodeEvent $event): void
    {
        $route = $event->route;

        if (!\in_array($route->getPath(), self::ROUTES, true)) {
            return;
        }

        $event->node->addEvent($this->eventFactory->create());
    }
}
