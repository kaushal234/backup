<?php

declare(strict_types=1);

namespace AppBundle\DataProvider\ION;

use ApiBundle\Client;

readonly class ItemMonologisticProvider
{
    public const ITEM_MONOLOGISTIC_URL = 'ion/item_monologistics';

    public function __construct(
        private Client $client,
    ) {
    }

    public function getItem(string $partNumber)
    {
        return $this->client->find(self::ITEM_MONOLOGISTIC_URL, $partNumber, [
            'query' => [
                'selection' => [
                    'description',
                    'itemCode',
                    'baseUOM',
                ],
            ],
        ]);
    }
}
