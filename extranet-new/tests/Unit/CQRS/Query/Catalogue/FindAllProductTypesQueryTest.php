<?php

declare(strict_types=1);

namespace App\Tests\Unit\CQRS\Query;

use App\CQRS\Query\Catalogue\FindAllProductTypesQuery;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class FindAllProductTypesQueryTest extends TestCase
{
    public function testInstance(): void
    {
        $query = new FindAllProductTypesQuery(['foo' => 'bar']);

        $this->assertSame(['foo' => 'bar'], $query->options);
    }
}
