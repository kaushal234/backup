<?php

declare(strict_types=1);

namespace App\ION\Event;

use Symfony\Contracts\EventDispatcher\Event;

class IONPreRequestEvent extends Event
{
    private array $parameters = [];

    public function __construct(private readonly string $resourceClass)
    {
    }

    public function getResourceClass(): string
    {
        return $this->resourceClass;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function addParameter(string $key, string $value): self
    {
        $this->parameters[$key] = $value;

        return $this;
    }
}
