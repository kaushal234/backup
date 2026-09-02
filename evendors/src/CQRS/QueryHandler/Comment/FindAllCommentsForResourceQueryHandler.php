<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\Comment;

use App\CQRS\Query\Comment\FindAllCommentsForResourceQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\ClientInterface;
use App\Sdk\Resource\Comment;
use Psl\Collection\AccessibleCollectionInterface;

final class FindAllCommentsForResourceQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly ClientInterface $client,
    ) {
    }

    /**
     * @return AccessibleCollectionInterface<int, Comment>
     */
    public function __invoke(FindAllCommentsForResourceQuery $query): AccessibleCollectionInterface
    {
        return $this->client->findAll(Comment::class, [
            'resource' => $query->resource->getIri(),
        ]);
    }
}
