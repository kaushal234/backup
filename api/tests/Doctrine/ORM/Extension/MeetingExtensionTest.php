<?php

declare(strict_types=1);

namespace App\Tests\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Metadata\GetCollection;
use App\Doctrine\ORM\Extension\MeetingExtension;
use App\Entity\Common\Subscription;
use App\Entity\Directory\People;
use App\Entity\MinutesOfMeeting\Action;
use App\Entity\MinutesOfMeeting\Meeting;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class MeetingExtensionTest extends TestCase
{
    use ProphecyTrait;

    public function testExtensionOnlyAppliesToMeetingsAndActions()
    {
        $emProphecy = $this->prophesize(EntityManagerInterface::class);
        $emProphecy->getExpressionBuilder()->willReturn(new Expr());
        $queryBuilder = new QueryBuilder($emProphecy->reveal());

        $queryBuilder->from(Meeting::class, 'o');

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->get(Security::class)->shouldNotBeCalled();

        $extension = new MeetingExtension($containerProphecy->reveal());

        $extension->applyToCollection($queryBuilder, new QueryNameGenerator(), \stdClass::class, new GetCollection());

        self::assertNull($queryBuilder->getDQLPart('where'));
    }

    public function testExtensionRestrictResultsForMeetings()
    {
        $emProphecy = $this->prophesize(EntityManagerInterface::class);
        $emProphecy->getExpressionBuilder()->willReturn(new Expr());
        $queryBuilder = new QueryBuilder($emProphecy->reveal());

        $queryBuilder->from(Meeting::class, 'o');

        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->getUser()->shouldBeCalledTimes(1)->willReturn(new People());

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());

        $extension = new MeetingExtension($containerProphecy->reveal());

        $extension->applyToCollection($queryBuilder, new QueryNameGenerator(), Meeting::class, new GetCollection());

        self::assertNotEmpty(array_filter($queryBuilder->getDQLPart('join')['o'], static fn (Join $join) => Subscription::class === $join->getJoin() && "CONCAT('/minutes_of_meeting/meetings/', o.id) = subscription_a4.resource" === $join->getCondition()));

        self::assertSame('o.confidential = :confidential OR o.createdBy = :user OR createdBy_a1.supervisor = :user OR supervisor_a2.supervisor = :user OR attendees_a3.id = :user OR subscription_a4.user = :user', (string) $queryBuilder->getDQLPart('where'));
    }

    public function testExtensionRestrictResultsForActions()
    {
        $emProphecy = $this->prophesize(EntityManagerInterface::class);
        $emProphecy->getExpressionBuilder()->willReturn(new Expr());
        $queryBuilder = new QueryBuilder($emProphecy->reveal());

        $queryBuilder->from(Meeting::class, 'o');

        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->getUser()->shouldBeCalledTimes(1)->willReturn(new People());

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());

        $extension = new MeetingExtension($containerProphecy->reveal());

        $extension->applyToCollection($queryBuilder, new QueryNameGenerator(), Action::class, new GetCollection());

        self::assertNotEmpty(array_filter($queryBuilder->getDQLPart('join')['o'], static fn (Join $join) => Subscription::class === $join->getJoin() && "CONCAT('/minutes_of_meeting/meetings/', meeting_a1.id) = subscription_a7.resource" === $join->getCondition()));

        self::assertSame('meeting_a1.confidential = :confidential OR meeting_a1.createdBy = :user OR createdBy_a2.supervisor = :user OR supervisor_a3.supervisor = :user OR attendees_a4.id = :user OR o.assignee = :user OR assignee_a5.supervisor = :user OR supervisor_a6.supervisor = :user OR subscription_a7.user = :user', (string) $queryBuilder->getDQLPart('where'));
    }
}
