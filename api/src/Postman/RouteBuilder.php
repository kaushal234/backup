<?php

declare(strict_types=1);

namespace App\Postman;

use App\Postman\Event\PostmanAllowedMethodsEvent;
use App\Postman\Factory\NodeFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Route;

class RouteBuilder
{
    public function __construct(
        private readonly NodeFactory $nodeFactory,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function build(Route $route): Collection
    {
        $nodes = new ArrayCollection();

        // Some route not have HTTP method register. Event to add possibility to manage specific cases.
        $event = new PostmanAllowedMethodsEvent($route);
        $this->eventDispatcher->dispatch($event);

        foreach (array_merge($route->getMethods(), $event->getAllowedMethods()) as $method) {
            $node = $this->nodeFactory->create($route, $method);
            $nodes->add($node);
        }

        return $nodes;
    }
}
