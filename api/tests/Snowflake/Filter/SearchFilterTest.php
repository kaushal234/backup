<?php

declare(strict_types=1);

namespace App\Tests\Snowflake\Filter;

use App\Http\SnowflakeClient;
use App\Snowflake\Filter\SearchFilter;
use App\Snowflake\QueryBuilder;
use App\Tests\Snowflake\Fixtures\DummyResource;
use PHPUnit\Framework\TestCase;

class SearchFilterTest extends TestCase
{
    public function testAppliesExactMatchForConfiguredProperty(): void
    {
        $snowflakeClient = $this->createMock(SnowflakeClient::class);
        $snowflakeClient->expects(self::once())
            ->method('query')
            ->with('SELECT * FROM A a WHERE c.SITE = ?', 'DB', 'SCHEMA', 'ROLE', ['1' => ['type' => 'TEXT', 'value' => '300']])
            ->willReturn([]);

        $queryBuilder = (new QueryBuilder($snowflakeClient))->from('A', 'a')->useConnection('DB', 'SCHEMA', 'ROLE');

        $filter = new SearchFilter(['site', 'item']);
        $filter->apply($queryBuilder, DummyResource::class, null, ['filters' => ['site' => '300']]);

        $queryBuilder->getResult();

        self::assertTrue(true);
    }

    public function testAppliesPartialMatchWithIlikeAndWildcards(): void
    {
        $snowflakeClient = $this->createMock(SnowflakeClient::class);
        $snowflakeClient->expects(self::once())
            ->method('query')
            ->with('SELECT * FROM A a WHERE i.DSCA ILIKE ?', 'DB', 'SCHEMA', 'ROLE', ['1' => ['type' => 'TEXT', 'value' => '%foo%']])
            ->willReturn([]);

        $queryBuilder = (new QueryBuilder($snowflakeClient))->from('A', 'a')->useConnection('DB', 'SCHEMA', 'ROLE');

        $filter = new SearchFilter(['description' => 'partial']);
        $filter->apply($queryBuilder, DummyResource::class, null, ['filters' => ['description' => 'foo']]);

        $queryBuilder->getResult();

        self::assertTrue(true);
    }

    public function testSkipsPropertiesNotConfiguredOrWithEmptyValue(): void
    {
        $snowflakeClient = $this->createMock(SnowflakeClient::class);
        $snowflakeClient->expects(self::once())
            ->method('query')
            ->with('SELECT * FROM A a', 'DB', 'SCHEMA', 'ROLE', [])
            ->willReturn([]);

        $queryBuilder = (new QueryBuilder($snowflakeClient))->from('A', 'a')->useConnection('DB', 'SCHEMA', 'ROLE');

        $filter = new SearchFilter(['site']);
        $filter->apply($queryBuilder, DummyResource::class, null, ['filters' => ['site' => '', 'item' => '43305281']]);

        $queryBuilder->getResult();

        self::assertTrue(true);
    }

    public function testAppliesMultipleConfiguredPropertiesInOrder(): void
    {
        $snowflakeClient = $this->createMock(SnowflakeClient::class);
        $snowflakeClient->expects(self::once())
            ->method('query')
            ->with(
                'SELECT * FROM A a WHERE c.SITE = ? AND c.ITEM = ?',
                'DB',
                'SCHEMA',
                'ROLE',
                [
                    '1' => ['type' => 'TEXT', 'value' => '300'],
                    '2' => ['type' => 'TEXT', 'value' => '43305281'],
                ],
            )
            ->willReturn([]);

        $queryBuilder = (new QueryBuilder($snowflakeClient))->from('A', 'a')->useConnection('DB', 'SCHEMA', 'ROLE');

        $filter = new SearchFilter(['site', 'item']);
        $filter->apply($queryBuilder, DummyResource::class, null, ['filters' => ['site' => '300', 'item' => '43305281']]);

        $queryBuilder->getResult();

        self::assertTrue(true);
    }

    public function testGetDescriptionNormalizesPlainListToExactStrategy(): void
    {
        $filter = new SearchFilter(['site', 'item']);

        self::assertSame(
            [
                'site' => ['property' => 'site', 'type' => 'string', 'required' => false, 'strategy' => 'exact'],
                'item' => ['property' => 'item', 'type' => 'string', 'required' => false, 'strategy' => 'exact'],
            ],
            $filter->getDescription(DummyResource::class),
        );
    }

    public function testGetDescriptionKeepsExplicitStrategy(): void
    {
        $filter = new SearchFilter(['description' => 'partial']);

        self::assertSame(
            ['description' => ['property' => 'description', 'type' => 'string', 'required' => false, 'strategy' => 'partial']],
            $filter->getDescription(DummyResource::class),
        );
    }
}
