<?php

declare(strict_types=1);

namespace App\Postman;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;

class Builder
{
    private const POSTMAN_ID = '6ca59bb8-3c77-494e-9890-32d974bfbb32';
    private const NAME = 'TLD';
    private const SCHEMA = 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json';

    public function __construct(
        private readonly RouterInterface $router,
        private readonly OrderStrategy $orderStrategy,
        private readonly RouteBuilder $routeBuilder,
        private readonly Finder $finder,
    ) {
    }

    public function build(): Collection
    {
        $collection = new ArrayCollection([
            'info' => [
                '_postman_id' => self::POSTMAN_ID,
                'name' => self::NAME,
                'schema' => self::SCHEMA,
            ],
            'item' => new ArrayCollection(),
        ]);

        /** @var Route $route */
        foreach ($this->router->getRouteCollection()->all() as $route) {
            $paths = $this->orderStrategy->getPathByNamespace($route);

            // Find place to add the route configuration
            $node = $this->finder->find($collection['item'], $paths);

            // Create the route configuration
            $routeNode = $this->routeBuilder->build($route);

            // Search existing configuration on the route
            $currentItem = $node->item ? $node->item->toArray() : [];

            // Add new route configuration
            $node->item = new ArrayCollection(array_merge($currentItem, $routeNode->toArray()));
        }

        return $collection;
    }
}
