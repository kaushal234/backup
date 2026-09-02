<?php

declare(strict_types=1);

namespace App\Tests\Report\Handler;

use App\Report\Handler\DefaultHandler;
use App\Report\ReportQueriesBuilder;
use App\Report\ReportQueriesBuilderFactory;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class DefaultHandlerTest extends TestCase
{
    use ProphecyTrait;

    public function testDefaultBehaviorIsNotToCallXAndYQueries()
    {
        $handler = new DefaultHandler();

        $queriesBuilderProphecy = $this->getQueriesBuilderProphecy(true, false, false);

        $factoryProphecy = $this->prophesize(ReportQueriesBuilderFactory::class);
        $factoryProphecy->getQueriesBuilder('americaine', 'rated', 'u do that')->shouldBeCalledTimes(1)->willReturn($queriesBuilderProphecy->reveal());

        $handler->setFactory($factoryProphecy->reveal());

        $result = $handler->handle('americaine', 'rated', 'u do that');

        self::assertNotNull($result->provideData());
        self::assertNull($result->provideXLabels());
        self::assertNull($result->provideYLabels());
    }

    public function testOptionsCanIncludeColumns()
    {
        $handler = new DefaultHandler();

        $queriesBuilderProphecy = $this->getQueriesBuilderProphecy(true, true, false);

        $factoryProphecy = $this->prophesize(ReportQueriesBuilderFactory::class);
        $factoryProphecy->getQueriesBuilder('americaine', 'rated', 'u do that')->shouldBeCalledTimes(1)->willReturn($queriesBuilderProphecy->reveal());

        $handler->setFactory($factoryProphecy->reveal());

        $result = $handler->handle('americaine', 'rated', 'u do that', ['includeAllColumns' => true]);

        self::assertNotNull($result->provideData());
        self::assertNotNull($result->provideXLabels());
        self::assertNull($result->provideYLabels());
    }

    public function testOptionsCanIncludeLines()
    {
        $handler = new DefaultHandler();

        $queriesBuilderProphecy = $this->getQueriesBuilderProphecy(true, false, true);

        $factoryProphecy = $this->prophesize(ReportQueriesBuilderFactory::class);
        $factoryProphecy->getQueriesBuilder('americaine', 'rated', 'u do that')->shouldBeCalledTimes(1)->willReturn($queriesBuilderProphecy->reveal());

        $handler->setFactory($factoryProphecy->reveal());

        $result = $handler->handle('americaine', 'rated', 'u do that', ['includeAllLines' => true]);

        self::assertNotNull($result->provideData());
        self::assertNull($result->provideXLabels());
        self::assertNotNull($result->provideYLabels());
    }

    public function testOptionsCanIncludeColumnsAndLines()
    {
        $handler = new DefaultHandler();

        $queriesBuilderProphecy = $this->getQueriesBuilderProphecy(true, true, true);

        $factoryProphecy = $this->prophesize(ReportQueriesBuilderFactory::class);
        $factoryProphecy->getQueriesBuilder('americaine', 'rated', 'u do that')->shouldBeCalledTimes(1)->willReturn($queriesBuilderProphecy->reveal());

        $handler->setFactory($factoryProphecy->reveal());

        $result = $handler->handle('americaine', 'rated', 'u do that', ['includeAllColumns' => true, 'includeAllLines' => true]);

        self::assertNotNull($result->provideData());
        self::assertNotNull($result->provideXLabels());
        self::assertNotNull($result->provideYLabels());
    }

    private function getQueriesBuilderProphecy(bool $main, bool $x, bool $y): ObjectProphecy
    {
        $queriesBuilderProphecy = $this->prophesize(ReportQueriesBuilder::class);

        foreach (['getMainQueryBuilder' => $main, 'getXQueryBuilder' => $x, 'getYQueryBuilder' => $y] as $method => $called) {
            if ($called) {
                $queryProphecy = $this->prophesize(Query::class);
                $queryProphecy->getScalarResult()->shouldBeCalledTimes(1)->willReturn([]);
                $queryBuilderProphecy = $this->prophesize(QueryBuilder::class);
                $queryBuilderProphecy->getQuery()->shouldBeCalledTimes(1)->willReturn($queryProphecy->reveal());

                $queriesBuilderProphecy->{$method}()->shouldBeCalledTimes(1)->willReturn($queryBuilderProphecy->reveal());
            } else {
                $queriesBuilderProphecy->{$method}()->shouldNotBeCalled();
            }
        }

        return $queriesBuilderProphecy;
    }
}
