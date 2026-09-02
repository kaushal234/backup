<?php

declare(strict_types=1);

namespace App\Tests\Snowflake;

use App\Http\SnowflakeClient;
use App\Snowflake\QueryBuilder;
use PHPUnit\Framework\TestCase;

class QueryBuilderTest extends TestCase
{
    public function testGetResultBuildsSelectFromWithJoins(): void
    {
        $snowflakeClient = $this->createMock(SnowflakeClient::class);
        $snowflakeClient->expects(self::once())
            ->method('query')
            ->with(
                'SELECT c.SITE, c.ITEM FROM V_VENDOR_XREF_COSTS c LEFT JOIN V_VENDOR_XREF_ITEMS i ON i.ITEM = c.ITEM',
                'LN_PRD',
                'SILVER',
                'READONLY',
                [],
            )
            ->willReturn([['SITE' => '300']]);

        $queryBuilder = (new QueryBuilder($snowflakeClient))
            ->select('c.SITE', 'c.ITEM')
            ->from('V_VENDOR_XREF_COSTS', 'c')
            ->leftJoin('V_VENDOR_XREF_ITEMS', 'i', 'i.ITEM = c.ITEM')
            ->useConnection('LN_PRD', 'SILVER', 'READONLY');

        self::assertSame([['SITE' => '300']], $queryBuilder->getResult());
    }

    public function testInnerAndRightJoinAreBuiltCorrectly(): void
    {
        $snowflakeClient = $this->createMock(SnowflakeClient::class);
        $snowflakeClient->expects(self::once())
            ->method('query')
            ->with('SELECT * FROM A a INNER JOIN B b ON b.a_id = a.id RIGHT JOIN C c ON c.b_id = b.id', 'DB', 'SCHEMA', 'ROLE', [])
            ->willReturn([]);

        (new QueryBuilder($snowflakeClient))
            ->from('A', 'a')
            ->useConnection('DB', 'SCHEMA', 'ROLE')
            ->innerJoin('B', 'b', 'b.a_id = a.id')
            ->rightJoin('C', 'c', 'c.b_id = b.id')
            ->getResult();

        self::assertTrue(true);
    }

    public function testAndWhereAppendsPositionalBindingsWithInferredTypes(): void
    {
        $snowflakeClient = $this->createMock(SnowflakeClient::class);
        $snowflakeClient->expects(self::once())
            ->method('query')
            ->with(
                'SELECT * FROM A a WHERE a.SITE = ? AND a.QTY = ? AND a.PRICE = ? AND a.ACTIVE = ? AND a.NOTE = ?',
                'DB',
                'SCHEMA',
                'ROLE',
                [
                    '1' => ['type' => 'TEXT', 'value' => '300'],
                    '2' => ['type' => 'FIXED', 'value' => '10'],
                    '3' => ['type' => 'REAL', 'value' => '1.5'],
                    '4' => ['type' => 'BOOLEAN', 'value' => '1'],
                    '5' => ['type' => 'TEXT', 'value' => ''],
                ],
            )
            ->willReturn([]);

        (new QueryBuilder($snowflakeClient))
            ->from('A', 'a')
            ->useConnection('DB', 'SCHEMA', 'ROLE')
            ->andWhere('a.SITE = ?', ['300'])
            ->andWhere('a.QTY = ?', [10])
            ->andWhere('a.PRICE = ?', [1.5])
            ->andWhere('a.ACTIVE = ?', [true])
            ->andWhere('a.NOTE = ?', [null])
            ->getResult();

        self::assertTrue(true);
    }

    public function testMultipleWhereConditionsAreJoinedWithAnd(): void
    {
        $snowflakeClient = $this->createMock(SnowflakeClient::class);
        $snowflakeClient->expects(self::once())
            ->method('query')
            ->with('SELECT * FROM A a WHERE a.SITE = ? AND a.ITEM ILIKE ?', 'DB', 'SCHEMA', 'ROLE', self::anything())
            ->willReturn([]);

        (new QueryBuilder($snowflakeClient))
            ->from('A', 'a')
            ->useConnection('DB', 'SCHEMA', 'ROLE')
            ->andWhere('a.SITE = ?', ['300'])
            ->andWhere('a.ITEM ILIKE ?', ['%foo%'])
            ->getResult();

        self::assertTrue(true);
    }

    public function testOrderByAppendsColumnsWithUppercasedDirection(): void
    {
        $snowflakeClient = $this->createMock(SnowflakeClient::class);
        $snowflakeClient->expects(self::once())
            ->method('query')
            ->with('SELECT * FROM A a ORDER BY a.SITE ASC, a.PRICE DESC', 'DB', 'SCHEMA', 'ROLE', [])
            ->willReturn([]);

        (new QueryBuilder($snowflakeClient))
            ->from('A', 'a')
            ->useConnection('DB', 'SCHEMA', 'ROLE')
            ->orderBy('a.SITE', 'asc')
            ->orderBy('a.PRICE', 'DESC')
            ->getResult();

        self::assertTrue(true);
    }

    public function testLimitAndOffsetAreOnlyAppendedWhenSet(): void
    {
        $snowflakeClient = $this->createMock(SnowflakeClient::class);
        $snowflakeClient->expects(self::once())
            ->method('query')
            ->with('SELECT * FROM A a LIMIT 10 OFFSET 20', 'DB', 'SCHEMA', 'ROLE', [])
            ->willReturn([]);

        (new QueryBuilder($snowflakeClient))
            ->from('A', 'a')
            ->useConnection('DB', 'SCHEMA', 'ROLE')
            ->setMaxResults(10)
            ->setFirstResult(20)
            ->getResult();

        self::assertTrue(true);
    }

    public function testNoLimitOrOffsetClauseWhenNeitherIsSet(): void
    {
        $snowflakeClient = $this->createMock(SnowflakeClient::class);
        $snowflakeClient->expects(self::once())
            ->method('query')
            ->with('SELECT * FROM A a', 'DB', 'SCHEMA', 'ROLE', [])
            ->willReturn([]);

        (new QueryBuilder($snowflakeClient))->from('A', 'a')->useConnection('DB', 'SCHEMA', 'ROLE')->getResult();

        self::assertTrue(true);
    }

    public function testGetCountWrapsQueryWithoutLimitOrOffsetButKeepsBindings(): void
    {
        $snowflakeClient = $this->createMock(SnowflakeClient::class);
        $snowflakeClient->expects(self::once())
            ->method('query')
            ->with(
                'SELECT COUNT(*) AS CNT FROM (SELECT * FROM A a WHERE a.SITE = ?) AS COUNT_QUERY',
                'DB',
                'SCHEMA',
                'ROLE',
                ['1' => ['type' => 'TEXT', 'value' => '300']],
            )
            ->willReturn([['CNT' => '42']]);

        $queryBuilder = (new QueryBuilder($snowflakeClient))
            ->from('A', 'a')
            ->useConnection('DB', 'SCHEMA', 'ROLE')
            ->andWhere('a.SITE = ?', ['300'])
            ->setMaxResults(10)
            ->setFirstResult(20);

        self::assertSame(42, $queryBuilder->getCount());
    }

    public function testGetCountReturnsZeroWhenNoRowIsReturned(): void
    {
        $snowflakeClient = $this->createMock(SnowflakeClient::class);
        $snowflakeClient->method('query')->willReturn([]);

        $queryBuilder = (new QueryBuilder($snowflakeClient))->from('A', 'a')->useConnection('DB', 'SCHEMA', 'ROLE');

        self::assertSame(0, $queryBuilder->getCount());
    }

    public function testGetResultThrowsWhenFromWasNotCalled(): void
    {
        $snowflakeClient = $this->createMock(SnowflakeClient::class);
        $snowflakeClient->expects(self::never())->method('query');

        self::expectException(\LogicException::class);
        self::expectExceptionMessage('The query builder has no FROM table: call from() first.');

        (new QueryBuilder($snowflakeClient))->useConnection('DB', 'SCHEMA', 'ROLE')->getResult();
    }

    public function testGetResultThrowsWhenUseConnectionWasNotCalled(): void
    {
        $snowflakeClient = $this->createMock(SnowflakeClient::class);
        $snowflakeClient->expects(self::never())->method('query');

        self::expectException(\LogicException::class);
        self::expectExceptionMessage('The query builder has no Snowflake connection: call useConnection() first.');

        (new QueryBuilder($snowflakeClient))->from('A', 'a')->getResult();
    }

    public function testGetCountThrowsWhenUseConnectionWasNotCalled(): void
    {
        $snowflakeClient = $this->createMock(SnowflakeClient::class);
        $snowflakeClient->expects(self::never())->method('query');

        self::expectException(\LogicException::class);
        self::expectExceptionMessage('The query builder has no Snowflake connection: call useConnection() first.');

        (new QueryBuilder($snowflakeClient))->from('A', 'a')->getCount();
    }

    public function testSelectDefaultsToStar(): void
    {
        $snowflakeClient = $this->createMock(SnowflakeClient::class);
        $snowflakeClient->expects(self::once())
            ->method('query')
            ->with('SELECT * FROM A a', 'DB', 'SCHEMA', 'ROLE', [])
            ->willReturn([]);

        (new QueryBuilder($snowflakeClient))->from('A', 'a')->useConnection('DB', 'SCHEMA', 'ROLE')->getResult();

        self::assertTrue(true);
    }
}
