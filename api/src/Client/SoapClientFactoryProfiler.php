<?php

declare(strict_types=1);

namespace App\Client;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SoapClientFactoryProfiler implements SoapClientFactoryInterface
{
    private readonly SoapClientFactory $decorated;
    private readonly EventDispatcherInterface $eventDispatcher;

    public function __construct(SoapClientFactory $decorated, EventDispatcherInterface $eventDispatcher)
    {
        $this->decorated = $decorated;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function createClient(string $clientName, string $resourceName): SoapClientInterface
    {
        return new SoapClientProfiler($this->decorated->createClient($clientName, $resourceName), $this->eventDispatcher);
    }

    public function getClient(string $resourceName): ?SoapClientInterface
    {
        return $this->decorated->getClient($resourceName);
    }

    public function getClients(): array
    {
        return $this->decorated->getClients();
    }

    public function disableAuthentication(): void
    {
        $this->decorated->disableAuthentication();
    }
}
