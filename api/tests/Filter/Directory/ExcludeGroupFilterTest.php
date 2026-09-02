<?php

declare(strict_types=1);

namespace App\Tests\Filter\Directory;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Directory\People;
use App\Filter\Directory\ExcludeGroupFilter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ExcludeGroupFilterTest extends TestCase
{
    use ProphecyTrait;

    public function testQueryBuilderIsModifiedWhenFilterIsApplied()
    {
        $emProphecy = $this->prophesize(EntityManagerInterface::class);
        $emProphecy->getExpressionBuilder()->willReturn(new Expr());
        $queryBuilder = new QueryBuilder($emProphecy->reveal());
        $queryBuilder->from(People::class, 'p');

        $queryNameGeneratorProphecy = $this->prophesize(QueryNameGeneratorInterface::class);

        $requestStackProphecy = $this->prophesize(RequestStack::class);

        $requestStackProphecy->getCurrentRequest()->shouldBeCalledOnce()->willReturn(new Request(['excludeGroup' => 'GG_MIS']));

        $subQueryBuilder = new QueryBuilder($emProphecy->reveal());
        $emProphecy->createQueryBuilder()->shouldBeCalledOnce()->willReturn($subQueryBuilder);

        $filter = new ExcludeGroupFilter($requestStackProphecy->reveal(), $emProphecy->reveal());
        $filter->apply($queryBuilder, $queryNameGeneratorProphecy->reveal(), People::class);

        /** @var Andx $where */
        $where = $queryBuilder->getDQLPart('where');
        $this->assertInstanceOf(Andx::class, $where);

        /** @var Andx $andPart */
        $andPart = $where->getParts()[0];

        $this->assertSame('(SELECT COUNT(acl.id) FROM App\Entity\Acl acl LEFT JOIN App\Entity\Group g WITH g = acl.group WHERE g.name = :group AND p.id = acl.user) = 0', (string) $andPart);
    }
}
