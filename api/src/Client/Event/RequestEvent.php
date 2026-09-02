<?php

declare(strict_types=1);

namespace App\Client\Event;

use App\Client\SoapClientProfiler;
use Symfony\Contracts\EventDispatcher\Event;

class RequestEvent extends Event
{
    public SoapClientProfiler $client;
    public string $operation;
    public string $executionTime;
    public array $resource;

    public function __construct(SoapClientProfiler $client, string $operation, string $executionTime, array $resource)
    {
        $this->client = $client;
        $this->operation = $operation;
        $this->executionTime = $executionTime;
        $this->resource = $resource;
    }
}
