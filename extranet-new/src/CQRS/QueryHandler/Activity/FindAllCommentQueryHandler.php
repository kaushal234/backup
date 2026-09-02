<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\Activity;

use App\CQRS\Query\Activity\FindAllCommentQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\Client;
use App\Sdk\PageInterface;
use App\Sdk\Resource\Comment;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
class FindAllCommentQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly Client $client,
    ) {
    }

    public function __invoke(FindAllCommentQuery $query): PageInterface
    {
        return $this->client->paginate(
            resource: Comment::class,
            page: $query->page,
            itemsPerPage: $query->itemsPerPage,
            criteria: [
                ...$query->options,
            ]
        );
    }
}
