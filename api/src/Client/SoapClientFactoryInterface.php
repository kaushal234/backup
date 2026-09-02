<?php

declare(strict_types=1);

namespace App\Client;

interface SoapClientFactoryInterface
{
    public function createClient(string $clientName, string $resourceName): SoapClientInterface;

    public function getClient(string $resourceName): ?SoapClientInterface;

    public function getClients(): array;

    public function disableAuthentication(): void;
}
