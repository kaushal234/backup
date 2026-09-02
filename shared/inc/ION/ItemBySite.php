<?php

declare(strict_types=1);

use ApiBundle\Client;

class ItemBySite
{
    private Client $client;

    public function __construct()
    {
        global $kernel;
        $this->client = $kernel->getContainer()->get(Client::class);
    }

    public function getItem(int $erp, string $item)
    {
        $item = $this->client->get(sprintf('/ion/items/site=%d;item=%s', $erp, $item));
        $item['t_copr'] = (float) $item['standardPrice'];
        $item['t_dsca'] = $item['itemDescription'];

        return $item;
    }
}