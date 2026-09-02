<?php

declare(strict_types=1);

namespace App\Postman;

use Symfony\Component\Routing\Route;

class OrderStrategy
{
    public function getPathByNamespace(Route $route): array
    {
        $paths = explode('\\', $route->getDefault('_api_resource_class') ?? '');
        $currentValue = current($paths);

        // remove App directory from the path
        if ('App' === $currentValue) {
            $currentKey = key($paths);
            unset($paths[$currentKey]);
        }

        return $paths;
    }
}
