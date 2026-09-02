<?php

declare(strict_types=1);

namespace Legacy\Provider;

use ApiBundle\Client;

class CustomerServiceRecordProvider
{
    private Client $client;

    public function __construct()
    {
        global $kernel;

        $container = $kernel->getContainer();
        $this->client = $container->get(Client::class);
    }

    public function fineByLegacyId(int $legacyId)
    {
        return $this->client->findOneBy('service/customer_service_records', ['legacyId' => $legacyId]);
    }
}