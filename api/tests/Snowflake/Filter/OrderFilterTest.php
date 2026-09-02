<?php

declare(strict_types=1);

namespace App\Tests\Snowflake\Filter;

use App\Http\SnowflakeClient;
use App\Snowflake\Filter\OrderFilter;
use App\Snowflake\QueryBuilder;
use App\Tests\Snowflake\Fixtures\DummyResource;
use PHPUnit\Framework\TestCase;

class OrderFilterTest extends TestCase
{
    /**
     * Regression test: ApiPlatform's AttributeFilterPass always normalizes the
     * "properties" ApiFilter argument into an associative array keyed by property
     * name (e.g. ['site' => null, 'item' => null]) before injecting it here, even
     * when the #[ApiFilter] attribute was written with a plain list. getDescription()
     * must read the array KEYS, not iterate its values -- iterating values used to
     * silently produce "order[]" for every property instead of "order[site]", etc.,
     * which made StrictSearchListener reject any "order[...]" query parameter as
     * "not available for the resource".
     */
    public function testGetDescriptionExposesOrderKeyForEachConfiguredProperty(): void
    {
        $filter = new OrderFilter(['site' => null, 'item' => null, 'price' => null]);

        self::assertSame(
            [
                'order[site]' => ['property' => 'site', 'type' => 'string', 'required' => false, 'schema' => ['type' => 'string', 'enum' => ['asc', 'desc']]],
                'order[item]' => ['property' => 'item', 'type' => 'string', 'required' => false, 'schema' => ['type' => 'string', 'enum' => ['asc', 'desc']]],
                'order[price]' => ['property' => 'price', 'type' => 'string', 'required' => false, 'schema' => ['type' => 'string', 'enum' => ['asc', 'desc']]],
            ],
            $filter->getDescription(DummyResource::class),
        );
    }

    public function testGetDescriptionUsesCustomOrderParameterName(): void
    {
        $filter = new OrderFilter(['site' => null], orderParameterName: 'sort');

        self::assertSame(
            ['sort[site]' => ['property' => 'site', 'type' => 'string', 'required' => false, 'schema' => ['type' => 'string', 'enum' => ['asc', 'desc']]]],
            $filter->getDescription(DummyResource::class),
        );
    }

    public function testApplyOrdersByConfiguredPropertyAndUppercasesDirection(): void
    {
        $snowflakeClient = $this->createMock(SnowflakeClient::class);
        $snowflakeClient->expects(self::once())
            ->method('query')
            ->with('SELECT * FROM A a ORDER BY c.SITE ASC', 'DB', 'SCHEMA', 'ROLE', [])
            ->willReturn([]);

        $queryBuilder = (new QueryBuilder($snowflakeClient))->from('A', 'a')->useConnection('DB', 'SCHEMA', 'ROLE');

        $filter = new OrderFilter(['site' => null, 'item' => null]);
        $filter->apply($queryBuilder, DummyResource::class, null, ['filters' => ['order' => ['site' => 'asc']]]);

        $queryBuilder->getResult();

        self::assertTrue(true);
    }

    public function testApplyOrdersByMultiplePropertiesInGivenOrder(): void
    {
        $snowflakeClient = $this->createMock(SnowflakeClient::class);
        $snowflakeClient->expects(self::once())
            ->method('query')
            ->with('SELECT * FROM A a ORDER BY c.SITE ASC, c.ITEM DESC', 'DB', 'SCHEMA', 'ROLE', [])
            ->willReturn([]);

        $queryBuilder = (new QueryBuilder($snowflakeClient))->from('A', 'a')->useConnection('DB', 'SCHEMA', 'ROLE');

        $filter = new OrderFilter(['site' => null, 'item' => null]);
        $filter->apply($queryBuilder, DummyResource::class, null, ['filters' => ['order' => ['site' => 'asc', 'item' => 'desc']]]);

        $queryBuilder->getResult();

        self::assertTrue(true);
    }

    public function testApplyIgnoresPropertiesNotConfigured(): void
    {
        $snowflakeClient = $this->createMock(SnowflakeClient::class);
        $snowflakeClient->expects(self::once())
            ->method('query')
            ->with('SELECT * FROM A a', 'DB', 'SCHEMA', 'ROLE', [])
            ->willReturn([]);

        $queryBuilder = (new QueryBuilder($snowflakeClient))->from('A', 'a')->useConnection('DB', 'SCHEMA', 'ROLE');

        $filter = new OrderFilter(['site' => null]);
        $filter->apply($queryBuilder, DummyResource::class, null, ['filters' => ['order' => ['description' => 'asc']]]);

        $queryBuilder->getResult();

        self::assertTrue(true);
    }

    public function testApplyIgnoresInvalidDirection(): void
    {
        $snowflakeClient = $this->createMock(SnowflakeClient::class);
        $snowflakeClient->expects(self::once())
            ->method('query')
            ->with('SELECT * FROM A a', 'DB', 'SCHEMA', 'ROLE', [])
            ->willReturn([]);

        $queryBuilder = (new QueryBuilder($snowflakeClient))->from('A', 'a')->useConnection('DB', 'SCHEMA', 'ROLE');

        $filter = new OrderFilter(['site' => null]);
        $filter->apply($queryBuilder, DummyResource::class, null, ['filters' => ['order' => ['site' => 'sideways']]]);

        $queryBuilder->getResult();

        self::assertTrue(true);
    }

    public function testApplyDoesNothingWhenOrderContextIsNotAnArray(): void
    {
        $snowflakeClient = $this->createMock(SnowflakeClient::class);
        $snowflakeClient->expects(self::once())
            ->method('query')
            ->with('SELECT * FROM A a', 'DB', 'SCHEMA', 'ROLE', [])
            ->willReturn([]);

        $queryBuilder = (new QueryBuilder($snowflakeClient))->from('A', 'a')->useConnection('DB', 'SCHEMA', 'ROLE');

        $filter = new OrderFilter(['site' => null]);
        $filter->apply($queryBuilder, DummyResource::class, null, ['filters' => ['order' => 'not-an-array']]);

        $queryBuilder->getResult();

        self::assertTrue(true);
    }

    public function testApplyDoesNothingWhenNoOrderIsRequested(): void
    {
        $snowflakeClient = $this->createMock(SnowflakeClient::class);
        $snowflakeClient->expects(self::once())
            ->method('query')
            ->with('SELECT * FROM A a', 'DB', 'SCHEMA', 'ROLE', [])
            ->willReturn([]);

        $queryBuilder = (new QueryBuilder($snowflakeClient))->from('A', 'a')->useConnection('DB', 'SCHEMA', 'ROLE');

        $filter = new OrderFilter(['site' => null]);
        $filter->apply($queryBuilder, DummyResource::class, null, ['filters' => []]);

        $queryBuilder->getResult();

        self::assertTrue(true);
    }
}
