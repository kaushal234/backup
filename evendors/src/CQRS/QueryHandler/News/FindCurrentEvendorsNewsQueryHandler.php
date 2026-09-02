<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\News;

use App\CQRS\Query\News\FindCurrentEvendorsNewsQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\ClientInterface;
use App\Sdk\Resource\EvendorsNews;
use Psl\Collection\AccessibleCollectionInterface;

class FindCurrentEvendorsNewsQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly ClientInterface $client,
    ) {
    }

    /**
     * @return AccessibleCollectionInterface<EvendorsNews>
     */
    public function __invoke(FindCurrentEvendorsNewsQuery $query): AccessibleCollectionInterface
    {
        return $this->client->findAll(EvendorsNews::class);
    }
}
