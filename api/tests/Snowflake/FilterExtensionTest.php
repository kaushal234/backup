<?php

declare(strict_types=1);

namespace App\Tests\Snowflake;

use ApiPlatform\Metadata\GetCollection;
use App\Http\SnowflakeClient;
use App\Snowflake\Filter\FilterInterface;
use App\Snowflake\Filter\OrderFilter;
use App\Snowflake\FilterExtension;
use App\Snowflake\QueryBuilder;
use App\Tests\Snowflake\Fixtures\DummyResource;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class FilterExtensionTest extends TestCase
{
    public function testReturnsEarlyWhenOperationHasNoFilters(): void
    {
        $locator = $this->createMock(ContainerInterface::class);
        $locator->expects(self::never())->method('has');

        $queryBuilder = new QueryBuilder($this->createMock(SnowflakeClient::class));

        (new FilterExtension($locator))->applyToCollection($queryBuilder, DummyResource::class, new GetCollection(filters: []));

        self::assertTrue(true);
    }

    public function testReturnsEarlyWhenNoOperationIsGiven(): void
    {
        $locator = $this->createMock(ContainerInterface::class);
        $locator->expects(self::never())->method('has');

        $queryBuilder = new QueryBuilder($this->createMock(SnowflakeClient::class));

        (new FilterExtension($locator))->applyToCollection($queryBuilder, DummyResource::class, null);

        self::assertTrue(true);
    }

    public function testAppliesResolvedFilterWithOperationAndFiltersContext(): void
    {
        $filter = $this->createMock(FilterInterface::class);
        $queryBuilder = new QueryBuilder($this->createMock(SnowflakeClient::class));
        $operation = new GetCollection(filters: ['filter.search']);

        $filter->expects(self::once())
            ->method('apply')
            ->with($queryBuilder, DummyResource::class, $operation, ['filters' => ['site' => '300']]);

        $locator = $this->createMock(ContainerInterface::class);
        $locator->method('has')->with('filter.search')->willReturn(true);
        $locator->method('get')->with('filter.search')->willReturn($filter);

        (new FilterExtension($locator))->applyToCollection($queryBuilder, DummyResource::class, $operation, ['filters' => ['site' => '300']]);

        self::assertTrue(true);
    }

    public function testSkipsFilterIdsNotFoundInTheLocator(): void
    {
        $queryBuilder = new QueryBuilder($this->createMock(SnowflakeClient::class));

        $locator = $this->createMock(ContainerInterface::class);
        $locator->method('has')->with('filter.missing')->willReturn(false);
        $locator->expects(self::never())->method('get');

        (new FilterExtension($locator))->applyToCollection($queryBuilder, DummyResource::class, new GetCollection(filters: ['filter.missing']));

        self::assertTrue(true);
    }

    public function testSkipsServicesNotImplementingFilterInterface(): void
    {
        $queryBuilder = new QueryBuilder($this->createMock(SnowflakeClient::class));

        $locator = $this->createMock(ContainerInterface::class);
        $locator->method('has')->with('filter.other')->willReturn(true);
        $locator->method('get')->with('filter.other')->willReturn(new \stdClass());

        (new FilterExtension($locator))->applyToCollection($queryBuilder, DummyResource::class, new GetCollection(filters: ['filter.other']));

        self::assertTrue(true);
    }

    public function testOrderFiltersAreAlwaysAppliedAfterOtherFilters(): void
    {
        $queryBuilder = new QueryBuilder($this->createMock(SnowflakeClient::class));

        $calls = [];

        $searchFilter = $this->createMock(FilterInterface::class);
        $searchFilter->method('apply')->willReturnCallback(static function () use (&$calls): void {
            $calls[] = 'search';
        });

        $orderFilter = $this->createMock(OrderFilter::class);
        $orderFilter->method('apply')->willReturnCallback(static function () use (&$calls): void {
            $calls[] = 'order';
        });

        $locator = $this->createMock(ContainerInterface::class);
        $locator->method('has')->willReturn(true);
        $locator->method('get')->willReturnMap([
            ['filter.order', $orderFilter],
            ['filter.search', $searchFilter],
        ]);

        // "filter.order" is declared FIRST on the operation, to prove ordering is enforced
        // by FilterExtension regardless of declaration order.
        $operation = new GetCollection(filters: ['filter.order', 'filter.search']);

        (new FilterExtension($locator))->applyToCollection($queryBuilder, DummyResource::class, $operation, ['filters' => []]);

        self::assertSame(['search', 'order'], $calls);
    }
}
