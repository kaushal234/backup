<?php

declare(strict_types=1);

namespace App\Tests\Client;

use App\Client\SoapClientFactoryInterface;
use App\Client\SoapClientInterface;

class SoapClientFactoryStub implements SoapClientFactoryInterface
{
    private readonly SoapClientFactoryInterface $decorated;

    private readonly bool $record;

    public function __construct(SoapClientFactoryInterface $decorated, bool $record = false)
    {
        $this->decorated = $decorated;
        $this->record = $record;
    }

    public function createClient(string $clientName, string $resourceName): SoapClientInterface
    {
        if (!$this->record) {
            $this->disableAuthentication();
        }

        $client = $this->decorated->createClient($clientName, $resourceName);
        $client->disableSoapCalls();

        if ($this->record) {
            $client->enableRecord();
        }

        return $client;
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
