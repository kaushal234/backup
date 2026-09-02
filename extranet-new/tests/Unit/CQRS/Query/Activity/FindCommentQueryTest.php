<?php

declare(strict_types=1);

namespace App\Tests\Unit\CQRS\Query;

use App\CQRS\Query\Activity\FindCommentQuery;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class FindCommentQueryTest extends TestCase
{
    public function testInstance(): void
    {
        $query = new FindCommentQuery(42);

        $this->assertSame(42, $query->id);
    }
}
