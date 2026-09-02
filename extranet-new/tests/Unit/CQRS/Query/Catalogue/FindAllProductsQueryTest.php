<?php

declare(strict_types=1);

namespace App\Tests\Unit\CQRS\Query;

use App\CQRS\Query\Catalogue\FindAllProductsQuery;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class FindAllProductsQueryTest extends TestCase
{
    public function testInstance(): void
    {
        $query = new FindAllProductsQuery(['foo' => 'bar']);

        $this->assertSame(['foo' => 'bar'], $query->options);
    }
}
