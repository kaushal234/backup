<?php

declare(strict_types=1);

namespace App\Tests\Unit\CQRS\QueryHandler\Activity;

use App\CQRS\Query\Activity\FindAllCommentQuery;
use App\CQRS\QueryHandler\Activity\FindAllCommentQueryHandler;
use App\Sdk\Client;
use App\Sdk\PageInterface;
use App\Sdk\Resource\Comment;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class FindAllCommentQueryHandlerTest extends TestCase
{
    public function test(): void
    {
        $client = $this->createMock(Client::class);
        $page = $this->createMock(PageInterface::class);
        $client
            ->expects($this->once())
            ->method('paginate')
            ->with(Comment::class, 7, 42, ['foo' => 'bar'])
            ->willReturn($page)
        ;

        $queryHandler = new FindAllCommentQueryHandler($client);
        $queryHandler->__invoke(new FindAllCommentQuery(page: 7, itemsPerPage: 42, options: ['foo' => 'bar']));
    }
}
