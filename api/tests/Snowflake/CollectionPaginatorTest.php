<?php

declare(strict_types=1);

namespace App\Tests\Snowflake;

use App\Snowflake\CollectionPaginator;
use App\Tests\Snowflake\Fixtures\DummyResource;
use PHPUnit\Framework\TestCase;

class CollectionPaginatorTest extends TestCase
{
    public function testExposesPageAndTotalsAsProvided(): void
    {
        $items = [
            new DummyResource(site: '300', item: 'a'),
            new DummyResource(site: '420', item: 'b'),
        ];

        $paginator = new CollectionPaginator(items: $items, currentPage: 2.0, itemsPerPage: 10.0, totalItems: 25.0);

        self::assertSame(2.0, $paginator->getCurrentPage());
        self::assertSame(10.0, $paginator->getItemsPerPage());
        self::assertSame(25.0, $paginator->getTotalItems());
        self::assertCount(2, $paginator);
        self::assertSame($items, iterator_to_array($paginator->getIterator()));
    }

    public function testGetLastPageRoundsUp(): void
    {
        $paginator = new CollectionPaginator(items: [], currentPage: 1.0, itemsPerPage: 10.0, totalItems: 25.0);

        self::assertSame(3.0, $paginator->getLastPage());
    }

    public function testGetLastPageIsAtLeastOneEvenWithoutResults(): void
    {
        $paginator = new CollectionPaginator(items: [], currentPage: 1.0, itemsPerPage: 10.0, totalItems: 0.0);

        self::assertSame(1.0, $paginator->getLastPage());
    }

    public function testGetLastPageDoesNotDivideByZeroWhenPaginationIsDisabled(): void
    {
        // itemsPerPage is 0.0 when the base provider disables pagination (see
        // AbstractCollectionProvider::provide()); getLastPage() must not divide by zero.
        $paginator = new CollectionPaginator(items: [], currentPage: 1.0, itemsPerPage: 0.0, totalItems: 5.0);

        self::assertSame(1.0, $paginator->getLastPage());
    }
}
