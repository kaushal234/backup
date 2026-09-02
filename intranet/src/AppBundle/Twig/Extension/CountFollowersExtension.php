<?php

declare(strict_types=1);

namespace AppBundle\Twig\Extension;

use ApiBundle\Client;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class CountFollowersExtension extends AbstractExtension
{
    public const API_URL = 'subscriptions';

    public function __construct(
        private readonly Client $client,
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('countFollowers', [$this, 'countFollowers']),
        ];
    }

    public function countFollowers(string $resourceIri): int
    {
        $followers = $this->client->findBy(self::API_URL, [
            'resource' => $resourceIri,
        ]);

        return \count($followers);
    }
}
