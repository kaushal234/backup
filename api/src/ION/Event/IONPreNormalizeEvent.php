<?php

declare(strict_types=1);

namespace App\ION\Event;

use App\ION\Client\Request\LogicalExpression;
use Symfony\Contracts\EventDispatcher\Event;

class IONPreNormalizeEvent extends Event
{
    private readonly string $resourceClass;
    private readonly string $ionResource;
    private readonly LogicalExpression $logicalExpression;
    private array $dataArea = [];

    public function __construct(string $resourceClass, string $ionResource, LogicalExpression $logicalExpression)
    {
        $this->resourceClass = $resourceClass;
        $this->ionResource = $ionResource;
        $this->logicalExpression = $logicalExpression;
    }

    public function getResourceClass(): string
    {
        return $this->resourceClass;
    }

    public function getIonResource(): string
    {
        return $this->ionResource;
    }

    public function getLogicalExpression(): LogicalExpression
    {
        return $this->logicalExpression;
    }

    public function getDataArea(): array
    {
        return $this->dataArea;
    }

    public function addDataArea(string $key, string $value): self
    {
        $this->dataArea[$key] = $value;

        return $this;
    }
}
