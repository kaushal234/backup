<?php

declare(strict_types=1);

namespace AppBundle\Security\RoleProvider;

use ApiBundle\Client;
use ApiBundle\Model\User;
use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;
use Symfony\Contracts\Cache\CacheInterface;

class LocationProvider implements RoleProviderInterface
{
    private readonly Client $client;
    private readonly CacheInterface $cache;
    private readonly Inflector $inflector;

    public function __construct(Client $client, CacheInterface $arrayCache)
    {
        $this->client = $client;
        $this->cache = $arrayCache;
        $this->inflector = InflectorFactory::create()->build();
    }

    public function loadRolesByUser(User $user)
    {
        $id = (string) $user->getId();

        return $this->cache->get("location_provider_$id", function () {
            $roles = [];
            if (null === $user = $this->client->get('/me')) {
                return $roles;
            }

            foreach ($user['locationCapabilities'] as $capability => $valid) {
                if (true === $valid) {
                    $roles[] = 'LOCATION_'.mb_strtoupper($this->inflector->tableize($capability));
                }
            }

            return $roles;
        });
    }
}
