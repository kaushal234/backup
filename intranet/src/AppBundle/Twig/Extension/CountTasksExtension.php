<?php

declare(strict_types=1);

namespace AppBundle\Twig\Extension;

use ApiBundle\Client;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class CountTasksExtension extends AbstractExtension
{
    public const API_URL = 'tasks';

    public function __construct(
        private readonly Client $client,
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('countTasks', [$this, 'countTasks']),
        ];
    }

    public function countTasks(string $resourceId): int
    {
        $tasks = $this->client->findBy(self::API_URL, ['module.name' => 'TOC', 'referenceId' => $resourceId]);

        return \count($tasks);
    }
}
