<?php

declare(strict_types=1);

namespace App\Tests\Filter\MIS;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use App\Entity\Directory\People;
use App\Entity\Group;
use App\Filter\MIS\RestrictedTeamMembersGroupFilter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class RestrictedTeamMembersGroupFilterTest extends TestCase
{
    use ProphecyTrait;

    public function testFilterIsApplied()
    {
        $requestStackProphecy = $this->prophesize(RequestStack::class);
        $securityProphecy = $this->prophesize(Security::class);

        $requestStackProphecy->getCurrentRequest()->shouldBeCalledTimes(1)->willReturn(new Request(['restricted' => '1']));
        $securityProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($user = new People());

        $filter = new RestrictedTeamMembersGroupFilter($requestStackProphecy->reveal(), $securityProphecy->reveal());

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->from(Group::class, 'g');

        $filter->apply($queryBuilder, new QueryNameGenerator(), Group::class);

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

        self::assertInstanceOf(Comparison::class, $orParts[0]);
        self::assertSame('acls_a1.user = :user', (string) $orParts[0]);

        self::assertInstanceOf(Comparison::class, $orParts[1]);
        self::assertSame('acls_a4.user = :user', (string) $orParts[1]);
    }

    public function testFilterIsNotApplied()
    {
        $requestStackProphecy = $this->prophesize(RequestStack::class);
        $securityProphecy = $this->prophesize(Security::class);

        $requestStackProphecy->getCurrentRequest()->shouldBeCalledTimes(1)->willReturn(new Request(['restricted' => '0']));
        $securityProphecy->getUser()->shouldNotBeCalled();

        $filter = new RestrictedTeamMembersGroupFilter($requestStackProphecy->reveal(), $securityProphecy->reveal());

        $emProphecy = $this->prophesize(EntityManagerInterface::class);
        $emProphecy->getExpressionBuilder()->willReturn(new Expr());

        $queryBuilder = new QueryBuilder($emProphecy->reveal());
        $queryBuilder->from(Group::class, 'g');

        $filter->apply($queryBuilder, new QueryNameGenerator(), Group::class);
    }

    private function getQueryBuilder()
    {
        $emProphecy = $this->prophesize(EntityManagerInterface::class);
        $emProphecy->getExpressionBuilder()->willReturn(new Expr());

        return new QueryBuilder($emProphecy->reveal());
    }
}
