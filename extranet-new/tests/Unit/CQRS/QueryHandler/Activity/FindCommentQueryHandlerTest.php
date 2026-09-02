<?php

declare(strict_types=1);

namespace App\Tests\Unit\CQRS\QueryHandler\Activity;

use App\CQRS\Query\Activity\FindCommentQuery;
use App\CQRS\QueryHandler\Activity\FindCommentQueryHandler;
use App\Sdk\Client;
use App\Sdk\Resource\Comment;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class FindCommentQueryHandlerTest extends TestCase
{
    public function test(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects($this->once())->method('find')->with(Comment::class, [
            'resource_id' => 42,
        ]);

        $queryHandler = new FindCommentQueryHandler($client);
        $queryHandler->__invoke(new FindCommentQuery(42));
    }
}
