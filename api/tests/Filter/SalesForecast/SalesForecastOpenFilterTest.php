<?php

declare(strict_types=1);

namespace App\Tests\Filter\SalesForecast;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Sales\SalesForecast;
use App\Filter\SalesForecast\SalesForecastOpenFilter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Query\Expr\Func;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class SalesForecastOpenFilterTest extends TestCase
{
    use ProphecyTrait;

    public function testFilterThrowIfResourceIsNotSalesForecast()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('This filter is restricted to the Sales Forecasts resource');

        $filter = $this->getFilter(true);
        $queryNameGeneratorProphecy = $this->prophesize(QueryNameGeneratorInterface::class);

        $filter->apply($this->getQueryBuilder(), $queryNameGeneratorProphecy->reveal(), \stdClass::class);
    }

    /**
     * @dataProvider provideTrueValues
     */
    public function testFilterIsAppliedForOpenTrue($value)
    {
        $filter = $this->getFilter($value);
        $queryNameGeneratorProphecy = $this->prophesize(QueryNameGeneratorInterface::class);

        $queryBuilder = $this->getQueryBuilder();

        $filter->apply($queryBuilder, $queryNameGeneratorProphecy->reveal(), SalesForecast::class);

        /** @var Andx $where */
        $where = $queryBuilder->getDQLPart('where');
        self::assertInstanceOf(Andx::class, $where);

        $andParts = $where->getParts();

        self::assertCount(1, $andParts);
        /** @var Orx $orPart */
        $orPart = $andParts[0];
        self::assertInstanceOf(Orx::class, $orPart);

        $orParts = $orPart->getParts();
        self::assertCount(2, $orParts);

        self::assertInstanceOf(Func::class, $orParts[0]);
        self::assertSame('o.status IN(:open_statuses)', (string) $orParts[0]);

        self::assertInstanceOf(Comparison::class, $orParts[1]);
        self::assertSame('o.closedAt > :one_month_ago', (string) $orParts[1]);
    }

    /**
     * @dataProvider provideFalseValues
     */
    public function testFilterIsAppliedForOpenFalse($value)
    {
        $filter = $this->getFilter($value);
        $queryNameGeneratorProphecy = $this->prophesize(QueryNameGeneratorInterface::class);

        $queryBuilder = $this->getQueryBuilder();

        $filter->apply($queryBuilder, $queryNameGeneratorProphecy->reveal(), SalesForecast::class);

        /** @var Andx $where */
        $where = $queryBuilder->getDQLPart('where');
        self::assertInstanceOf(Andx::class, $where);

        $andParts = $where->getParts();

        self::assertCount(1, $andParts);
        self::assertSame('o.status NOT IN(:open_statuses)', (string) $andParts[0]);
    }

    public function provideTrueValues()
    {
        return [[true], ['true'], ['1']];
    }

    public function provideFalseValues()
    {
        return [[false], ['false'], ['0']];
    }

    private function getFilter($value)
    {
        $requestStackProphecy = $this->prophesize(RequestStack::class);

        $requestStackProphecy->getCurrentRequest()->shouldBeCalledTimes(1)->willReturn(new Request(['open' => $value]));

        return new SalesForecastOpenFilter($requestStackProphecy->reveal());
    }

    private function getQueryBuilder()
    {
        $emProphecy = $this->prophesize(EntityManagerInterface::class);
        $emProphecy->getExpressionBuilder()->willReturn(new Expr());

        return new QueryBuilder($emProphecy->reveal());
    }
}
