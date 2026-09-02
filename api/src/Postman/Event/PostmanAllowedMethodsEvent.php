<?php

declare(strict_types=1);

namespace App\Postman\Event;

use Symfony\Component\Routing\Route;

class PostmanAllowedMethodsEvent
{
    private array $allowedMethods = [];

    public function __construct(
        private readonly Route $route,
    ) {
    }

    public function addMethod(string $method): void
    {
        if (\in_array($method, $this->allowedMethods, true)) {
            return;
        }

        $this->allowedMethods[] = $method;
    }

    public function getAllowedMethods(): array
    {
        return $this->allowedMethods;
    }

    public function getRoute(): Route
    {
        return $this->route;
    }
}
