<?php

declare(strict_types=1);

namespace App\Tests\Link\QueryBuilder;

use App\Entity\EquipmentRecord;
use App\Entity\Sales\Demo;
use App\Link\QueryBuilder\GraphQLQueryBuilder;
use App\Link\ResourceSourceProvider\Support\EquipmentRecordResourceSourceProvider;
use App\Link\SourceProvider\SourceProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class GraphQLQueryBuilderTest extends TestCase
{
    use ProphecyTrait;

    public function testQueryIsCorrectlyConstructed()
    {
        $sourceProviderProphecy = $this->prophesize(SourceProvider::class);
        $equipmentRecordSourceProvider = $this->prophesize(EquipmentRecordResourceSourceProvider::class);

        $sourceProviderProphecy->getResourceSourceProvider(EquipmentRecord::class)->shouldBeCalledOnce()->willReturn($equipmentRecordSourceProvider->reveal());
        $equipmentRecordSourceProvider->getService()->shouldBeCalledOnce()->willReturn('foo');
        $equipmentRecordSourceProvider->getProperties()->shouldBeCalledOnce()->willReturn('bar foobar');

        $queryBuilder = new GraphQLQueryBuilder($sourceProviderProphecy->reveal());

        self::assertSame(<<<'GRAPHQL'
            {
              foo_findByFilter(params: { startIndex : 0, pageSize :-1 }
              ) { results {bar foobar}}
            }
            GRAPHQL,
            $queryBuilder->getListQuery(EquipmentRecord::class));
    }

    public function testExceptionIsThrownWhenResourceIsNotLinkInterface()
    {
        $sourceProviderProphecy = $this->prophesize(SourceProvider::class);

        $queryBuilder = new GraphQLQueryBuilder($sourceProviderProphecy->reveal());

        self::expectException(UnprocessableEntityHttpException::class);
        self::expectExceptionMessage('Object should be an instance of LinkResourceInterface.');

        $queryBuilder->getListQuery(Demo::class);
    }
}
