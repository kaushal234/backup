<?php

declare(strict_types=1);

namespace App\Tests\Filter\MinutesOfMeeting;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use App\Entity\Directory\People;
use App\Entity\MinutesOfMeeting\Meeting;
use App\Filter\MinutesOfMeeting\MyTeamMeetingFilter;
use App\Repository\Directory\PeopleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class MyTeamMeetingFilterTest extends TestCase
{
    use ProphecyTrait;

    public function testFilterIsApplied()
    {
        $requestStackProphecy = $this->prophesize(RequestStack::class);
        $peopleRepositoryMock = $this->getMockBuilder(PeopleRepository::class)->disableOriginalConstructor()->onlyMethods(['getSubordinates'])->getMock();
        $securityProphecy = $this->prophesize(Security::class);

        $requestStackProphecy->getCurrentRequest()->shouldBeCalledTimes(1)->willReturn(new Request(['myTeam' => '1']));
        $securityProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($user = new People());
        $subordinate = (new People())->setSupervisor($user);

        $refl = new \ReflectionClass($subordinate);

        /** @var \ReflectionClass $parentClass */
        $parentClass = $refl->getParentClass();

        $reflectionProperty = $parentClass->getProperty('id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($subordinate, 999);

        $peopleRepositoryMock->expects($this->once())->method('getSubordinates')->with($user, 2)->willReturn([$subordinate]);

        $filter = new MyTeamMeetingFilter($requestStackProphecy->reveal(), $peopleRepositoryMock, $securityProphecy->reveal());

        $emProphecy = $this->prophesize(EntityManagerInterface::class);
        $emProphecy->getExpressionBuilder()->willReturn(new Expr());

        $queryBuilder = new QueryBuilder($emProphecy->reveal());
        $queryBuilder->from(Meeting::class, 'm');

        $filter->apply($queryBuilder, new QueryNameGenerator(), Meeting::class);

        self::assertArrayHasKey('m', $joins = $queryBuilder->getDQLPart('join'));
        self::assertCount(2, $joins['m']);

        self::assertSame('LEFT JOIN m.attendees attendees_a1', (string) $joins['m'][0]);
        self::assertSame('LEFT JOIN m.createdBy createdBy_a2', (string) $joins['m'][1]);

        self::assertSame('attendees_a1.id IN(:subordinates) OR createdBy_a2.id IN(:subordinates)', (string) $queryBuilder->getDQLPart('where'));

        self::assertSame([999], $queryBuilder->getParameter('subordinates')->getValue());
    }

    public function testFilterIsNotApplied()
    {
        $requestStackProphecy = $this->prophesize(RequestStack::class);
        $peopleRepositoryMock = $this->createMock(PeopleRepository::class);
        $securityProphecy = $this->prophesize(Security::class);

        $requestStackProphecy->getCurrentRequest()->shouldBeCalledTimes(1)->willReturn(new Request(['myTeam' => '0']));
        $securityProphecy->getUser()->shouldBeCalledTimes(1)->willReturn(new People());

        $filter = new MyTeamMeetingFilter($requestStackProphecy->reveal(), $peopleRepositoryMock, $securityProphecy->reveal());

        $emProphecy = $this->prophesize(EntityManagerInterface::class);
        $emProphecy->getExpressionBuilder()->willReturn(new Expr());

        $queryBuilder = new QueryBuilder($emProphecy->reveal());
        $queryBuilder->from(Meeting::class, 'm');

        $filter->apply($queryBuilder, new QueryNameGenerator(), Meeting::class);
    }
}
