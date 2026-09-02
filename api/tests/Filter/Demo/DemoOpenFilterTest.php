<?php

declare(strict_types=1);

namespace App\Tests\Filter\Demo;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Sales\Demo;
use App\Filter\Demo\DemoOpenFilter;
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

class DemoOpenFilterTest extends TestCase
{
    use ProphecyTrait;

    public function testFilterThrowIfResourceIsNotDemo()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('This filter is restricted to the Demo resource');

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

        $filter->apply($queryBuilder, $queryNameGeneratorProphecy->reveal(), Demo::class);

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
        self::assertSame('o.closingDate > :two_months_ago', (string) $orParts[1]);
    }

    public function provideTrueValues()
    {
        return [[true], ['true'], ['1']];
    }

    private function getFilter($value)
    {
        $requestStackProphecy = $this->prophesize(RequestStack::class);

        $requestStackProphecy->getCurrentRequest()->shouldBeCalledTimes(1)->willReturn(new Request(['open' => $value]));

        return new DemoOpenFilter($requestStackProphecy->reveal());
    }

    private function getQueryBuilder()
    {
        $emProphecy = $this->prophesize(EntityManagerInterface::class);
        $emProphecy->getExpressionBuilder()->willReturn(new Expr());

        return new QueryBuilder($emProphecy->reveal());
    }
}
