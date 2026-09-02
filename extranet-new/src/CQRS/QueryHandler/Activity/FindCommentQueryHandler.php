<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\Activity;

use App\CQRS\Query\Activity\FindCommentQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\Client;
use App\Sdk\Resource\Comment;
use App\Sdk\Resource\ResourceInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
class FindCommentQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly Client $client
    ) {
    }

    public function __invoke(FindCommentQuery $query): ResourceInterface
    {
        return $this->client->find(Comment::class, ['resource_id' => $query->id]);
    }
}
