<?php

declare(strict_types=1);

namespace App\Tests\Filter\SalesForecast;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Sales\Customer;
use App\Entity\Sales\SalesForecast;
use App\Filter\SalesForecast\SalesForecastCustomerFilter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class SalesForecastCustomerFilterTest extends TestCase
{
    use ProphecyTrait;

    public function testFilterThrowIfResourceIsNotSalesForecast()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('This filter is restricted to the Sales Forecasts resource');

        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $requestStackProphecy = $this->prophesize(RequestStack::class);

        $requestStackProphecy->getCurrentRequest()->shouldBeCalledTimes(1)->willReturn(new Request(['customer' => '/sales/customers/1']));

        $filter = new SalesForecastCustomerFilter($iriConverterProphecy->reveal(), $requestStackProphecy->reveal());

        $queryNameGeneratorProphecy = $this->prophesize(QueryNameGeneratorInterface::class);

        $filter->apply($this->getQueryBuilder(), $queryNameGeneratorProphecy->reveal(), \stdClass::class);
    }

    public function testFilterIsApplied()
    {
        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $requestStackProphecy = $this->prophesize(RequestStack::class);

        $requestStackProphecy->getCurrentRequest()->shouldBeCalledTimes(1)->willReturn(new Request(['customer' => '/sales/customers/1']));
        $iriConverterProphecy->getResourceFromIri('/sales/customers/1')->shouldBeCalledTimes(1)->willReturn(new Customer());

        $filter = new SalesForecastCustomerFilter($iriConverterProphecy->reveal(), $requestStackProphecy->reveal());

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
        self::assertCount(3, $orParts);

        self::assertInstanceOf(Comparison::class, $orParts[0]);
        self::assertSame('o.buyer = :customer', (string) $orParts[0]);

        self::assertInstanceOf(Comparison::class, $orParts[1]);
        self::assertSame('o.endUser = :customer', (string) $orParts[1]);

        self::assertInstanceOf(Comparison::class, $orParts[1]);
        self::assertSame('o.thirdParty = :customer', (string) $orParts[2]);
    }

    private function getQueryBuilder()
    {
        $emProphecy = $this->prophesize(EntityManagerInterface::class);
        $emProphecy->getExpressionBuilder()->willReturn(new Expr());

        return new QueryBuilder($emProphecy->reveal());
    }
}
