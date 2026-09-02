<?php

declare(strict_types=1);

namespace App\Postman\Factory;

use App\Postman\Event\NodeEvent;
use App\Postman\Resource\Node;
use App\Postman\Resource\Response;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Route;

class NodeFactory
{
    public function __construct(
        private readonly RequestFactory $requestFactory,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function create(Route $route, string $method): Node
    {
        $config = new Node();
        $config->name = $route->getPath();
        $config->request = $this->requestFactory->create($route, $method);
        $config->response = new Response();

        $event = new NodeEvent($config, $route, $method);
        $this->eventDispatcher->dispatch($event);

        return $config;
    }
}
