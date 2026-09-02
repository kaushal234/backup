<?php

declare(strict_types=1);

namespace App\Tests\Unit\CQRS\Query;

use App\CQRS\Query\Activity\FindAllCommentQuery;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class FindAllCommentQueryTest extends TestCase
{
    public function testInstance(): void
    {
        $query = new FindAllCommentQuery(page: 7, itemsPerPage: 42, options: ['foo' => 'bar']);

        $this->assertSame(7, $query->page);
        $this->assertSame(42, $query->itemsPerPage);
        $this->assertSame(['foo' => 'bar'], $query->options);
    }
}
