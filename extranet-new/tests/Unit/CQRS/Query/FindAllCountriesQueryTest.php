<?php

declare(strict_types=1);

namespace App\Tests\Unit\CQRS\Query;

use App\CQRS\Query\FindAllCountriesQuery;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class FindAllCountriesQueryTest extends TestCase
{
    public function testInstance(): void
    {
        $query = new FindAllCountriesQuery(['foo' => 'bar']);

        $this->assertSame(['foo' => 'bar'], $query->options);
    }
}
