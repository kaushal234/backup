<?php

declare(strict_types=1);

namespace App\Tests\Filter\Customer;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Sales\Customer;
use App\Filter\Customer\CustomerActiveFilter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Func;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CustomerActiveFilterTest extends TestCase
{
    use ProphecyTrait;

    public function testFilterThrowIfResourceIsNotDemo()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('This filter is restricted to the Customer resource');

        $filter = $this->getFilter(true);
        $queryNameGeneratorProphecy = $this->prophesize(QueryNameGeneratorInterface::class);

        $filter->apply($this->getQueryBuilder(), $queryNameGeneratorProphecy->reveal(), \stdClass::class);
    }

    /**
     * @dataProvider provideTrueValues
     */
    public function testFilterIsAppliedForActiveTrue($value)
    {
        $filter = $this->getFilter($value);
        $queryNameGeneratorProphecy = $this->prophesize(QueryNameGeneratorInterface::class);

        $queryBuilder = $this->getQueryBuilder();

        $filter->apply($queryBuilder, $queryNameGeneratorProphecy->reveal(), Customer::class);

        /** @var Andx $where */
        $where = $queryBuilder->getDQLPart('where');
        self::assertInstanceOf(Andx::class, $where);

        $andParts = $where->getParts();

        self::assertCount(1, $andParts);
        self::assertInstanceOf(Func::class, $andParts[0]);
        self::assertSame('o.status IN(:active_statuses)', (string) $andParts[0]);
    }

    public function provideTrueValues()
    {
        return [[true], ['true'], ['1']];
    }

    private function getFilter($value)
    {
        $requestStackProphecy = $this->prophesize(RequestStack::class);

        $requestStackProphecy->getCurrentRequest()->shouldBeCalledTimes(1)->willReturn(new Request(['active' => $value]));

        return new CustomerActiveFilter($requestStackProphecy->reveal());
    }

    private function getQueryBuilder()
    {
        $emProphecy = $this->prophesize(EntityManagerInterface::class);
        $emProphecy->getExpressionBuilder()->willReturn(new Expr());

        return new QueryBuilder($emProphecy->reveal());
    }
}
