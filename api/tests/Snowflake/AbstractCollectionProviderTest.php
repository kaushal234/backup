<?php

declare(strict_types=1);

namespace App\Tests\Snowflake;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\State\Pagination\Pagination;
use App\Http\SnowflakeClient;
use App\Snowflake\CollectionPaginator;
use App\Snowflake\FilterExtension;
use App\Tests\Snowflake\Fixtures\DummyResource;
use App\Tests\Snowflake\Fixtures\TestCollectionProvider;
use PHPUnit\Framework\TestCase;

class AbstractCollectionProviderTest extends TestCase
{
    public function testProvideRunsDataAndCountQueriesAndAppliesPaginationWhenEnabled(): void
    {
        $snowflakeClient = $this->createMock(SnowflakeClient::class);
        $snowflakeClient->expects(self::exactly(2))
            ->method('query')
            ->willReturnOnConsecutiveCalls(
                [['SITE' => '300', 'ITEM' => '1']],
                [['CNT' => '42']],
            );

        $filterExtension = $this->createMock(FilterExtension::class);
        $filterExtension->expects(self::once())->method('applyToCollection');

        // Real Pagination (it's a final class): 5 items per page, page 1 by default.
        $pagination = new Pagination(['items_per_page' => 5]);

        $provider = $this->createProvider($snowflakeClient, $filterExtension, $pagination);

        $paginator = $provider->provide(new GetCollection());

        self::assertInstanceOf(CollectionPaginator::class, $paginator);
        self::assertSame(1.0, $paginator->getCurrentPage());
        self::assertSame(5.0, $paginator->getItemsPerPage());
        self::assertSame(42.0, $paginator->getTotalItems());
        self::assertCount(1, $paginator);

        [$item] = iterator_to_array($paginator->getIterator());
        self::assertInstanceOf(DummyResource::class, $item);
        self::assertSame('300', $item->site);
        self::assertSame('1', $item->item);
    }

    public function testProvideSkipsCountQueryWhenPaginationIsDisabled(): void
    {
        $snowflakeClient = $this->createMock(SnowflakeClient::class);
        $snowflakeClient->expects(self::once())
            ->method('query')
            ->willReturn([
                ['SITE' => '300', 'ITEM' => '1'],
                ['SITE' => '420', 'ITEM' => '2'],
            ]);

        $filterExtension = $this->createMock(FilterExtension::class);
        $pagination = new Pagination();

        $provider = $this->createProvider($snowflakeClient, $filterExtension, $pagination);

        $paginator = $provider->provide(new GetCollection(paginationEnabled: false));

        self::assertSame(2.0, $paginator->getTotalItems());
        self::assertSame(2.0, $paginator->getItemsPerPage());
        self::assertCount(2, $paginator);
    }

    public function testColumnValueResolvesRowKeyFromColumnAlias(): void
    {
        $snowflakeClient = $this->createMock(SnowflakeClient::class);
        $filterExtension = $this->createMock(FilterExtension::class);
        $pagination = new Pagination();

        $provider = $this->createProvider($snowflakeClient, $filterExtension, $pagination);

        self::assertSame('300', $provider->readColumnValue(['SITE' => '300'], 'site'));
        self::assertNull($provider->readColumnValue([], 'site'));
        self::assertSame('some text', $provider->readColumnValue(['DSCA' => 'some text'], 'description'));
    }

    private function createProvider(SnowflakeClient $snowflakeClient, FilterExtension $filterExtension, Pagination $pagination): TestCollectionProvider
    {
        return new TestCollectionProvider($snowflakeClient, $filterExtension, $pagination);
    }
}
