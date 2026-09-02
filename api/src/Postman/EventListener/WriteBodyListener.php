<?php

declare(strict_types=1);

namespace App\Postman\EventListener;

use App\Postman\Event\NodeEvent;
use App\Postman\Factory\JsonBodyFactory;
use App\Postman\WritePropertyInfo;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;

class WriteBodyListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly WritePropertyInfo $writePropertyInfo,
        private readonly JsonBodyFactory $jsonBodyFactory,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            NodeEvent::class => ['bodyForWriteRoutes', 100],
        ];
    }

    public function bodyForWriteRoutes(NodeEvent $event): void
    {
        if (!\in_array($event->method, [Request::METHOD_POST, Request::METHOD_PUT], true)) {
            return;
        }

        $denormalizeFields = $this->writePropertyInfo->getStructuredFields($event->route);
        $event->node->request->body = $this->jsonBodyFactory->create($denormalizeFields);
    }
}
