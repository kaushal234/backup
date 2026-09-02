<?php

declare(strict_types=1);

namespace AppBundle\Service;

use ApiBundle\Client;
use ApiBundle\Hydra\HydraCollection;

class DataProvider
{
    /** @var Client */
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $resource
     *
     * @return HydraCollection|\Generator
     */
    public function findAll($resource, array $query = [], array $orders = [])
    {
        return $this->client->findAll($resource, $query, $orders);
    }

    /**
     * @param string $resource
     *
     * @return HydraCollection|\Generator
     */
    public function findAllFromBaan($resource, array $query = [], array $orders = [], array $headers = [])
    {
        $paginationDisabled = isset($query['pagination']) && false === $query['pagination'];

        $page = 0;
        do {
            if (++$page > 1 && !$paginationDisabled) {
                $query['page'] = $page;
            }

            try {
                $collection = $this->client->findBy($resource, $query, $orders, $headers);
            } catch (\Exception $e) {
                $collection = [];
            }

            foreach ($collection as $row) {
                yield $row;
            }
        } while (!$paginationDisabled && !empty($collection));
    }
}
