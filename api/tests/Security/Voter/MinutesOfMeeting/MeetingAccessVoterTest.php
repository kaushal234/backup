<?php

declare(strict_types=1);

namespace App\Tests\Security\Voter\MinutesOfMeeting;

use App\Entity\Directory\People;
use App\Entity\MinutesOfMeeting\Meeting;
use App\Repository\Common\SubscriptionRepository;
use App\Security\Voter\MinutesOfMeeting\MeetingAccessVoter;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class MeetingAccessVoterTest extends TestCase
{
    use ProphecyTrait;

    public function testPeopleWhoCreateMeetingAccess()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $securityProphecy = $this->prophesize(Security::class);
        $subscriptionRepositoryMock = $this->getMockBuilder(SubscriptionRepository::class)->disableOriginalConstructor()->onlyMethods(['isFollowingResource'])->getMock();
        $subscriptionRepositoryMock->expects($this->never())->method('isFollowingResource');

        $tokenProphecy = $this->prophesize(TokenInterface::class);
        $tokenProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($people = new People());

        $meeting = new Meeting();
        $meeting->setCreatedBy($people);
        $meeting->setConfidential(true);

        $serviceLocatorProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());
        $securityProphecy->isGranted('FEATURE_MEETING_READ', $meeting)->shouldBeCalledTimes(1)->willReturn(false);
        $securityProphecy->isGranted('MOO_MOM')->shouldBeCalledTimes(1)->willReturn(false);

        $voter = new MeetingAccessVoter($serviceLocatorProphecy->reveal());

        $result = $voter->vote($tokenProphecy->reveal(), $meeting, ['MEETING_READ_VOTER']);

        self::assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testSupervisorOfPeopleWhoCreateMeetingAccess()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $subscriptionRepositoryMock = $this->getMockBuilder(SubscriptionRepository::class)->disableOriginalConstructor()->onlyMethods(['isFollowingResource'])->getMock();
        $subscriptionRepositoryMock->expects($this->never())->method('isFollowingResource');
        $securityProphecy = $this->prophesize(Security::class);

        $tokenProphecy = $this->prophesize(TokenInterface::class);
        $tokenProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($people = new People());

        $supervisor = new People();
        $people->setSupervisor($supervisor);
        $meeting = new Meeting();
        $meeting->setCreatedBy($people);
        $meeting->setConfidential(true);

        $serviceLocatorProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());
        $securityProphecy->isGranted('FEATURE_MEETING_READ', $meeting)->shouldBeCalledTimes(1)->willReturn(false);
        $securityProphecy->isGranted('MOO_MOM')->shouldBeCalledTimes(1)->willReturn(false);

        $voter = new MeetingAccessVoter($serviceLocatorProphecy->reveal());

        $result = $voter->vote($tokenProphecy->reveal(), $meeting, ['MEETING_READ_VOTER']);

        self::assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testSupervisorOfSupervisorOfPeopleWhoCreateMeetingAccess()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $subscriptionRepositoryMock = $this->getMockBuilder(SubscriptionRepository::class)->disableOriginalConstructor()->onlyMethods(['isFollowingResource'])->getMock();
        $subscriptionRepositoryMock->expects($this->never())->method('isFollowingResource');
        $securityProphecy = $this->prophesize(Security::class);

        $tokenProphecy = $this->prophesize(TokenInterface::class);
        $tokenProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($people = new People());

        $meeting = new Meeting();
        $supervisor = new People();
        $director = new People();
        $supervisor->setSupervisor($director);
        $people->setSupervisor($supervisor);
        $meeting->setCreatedBy($people);
        $meeting->setConfidential(true);

        $serviceLocatorProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());
        $securityProphecy->isGranted('FEATURE_MEETING_READ', $meeting)->shouldBeCalledTimes(1)->willReturn(false);
        $securityProphecy->isGranted('MOO_MOM')->shouldBeCalledTimes(1)->willReturn(false);

        $voter = new MeetingAccessVoter($serviceLocatorProphecy->reveal());

        $result = $voter->vote($tokenProphecy->reveal(), $meeting, ['MEETING_READ_VOTER']);

        self::assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testSubscriberOfMeetingAccess()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $subscriptionRepositoryMock = $this->getMockBuilder(SubscriptionRepository::class)->disableOriginalConstructor()->onlyMethods(['isFollowingResource'])->getMock();
        $securityProphecy = $this->prophesize(Security::class);

        $tokenProphecy = $this->prophesize(TokenInterface::class);
        $tokenProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($people = new People());

        $meeting = new Meeting();
        $creator = new People();
        $meeting->setCreatedBy($creator);
        $meeting->setConfidential(true);

        $serviceLocatorProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());
        $securityProphecy->isGranted('FEATURE_MEETING_READ', $meeting)->shouldBeCalledTimes(1)->willReturn(false);
        $securityProphecy->isGranted('MOO_MOM')->shouldBeCalledTimes(1)->willReturn(false);

        $serviceLocatorProphecy->get(SubscriptionRepository::class)->shouldBeCalledTimes(1)->willReturn($subscriptionRepositoryMock);
        $subscriptionRepositoryMock->expects($this->once())->method('isFollowingResource')->with($people, $meeting)->willReturn(true);

        $voter = new MeetingAccessVoter($serviceLocatorProphecy->reveal());

        $result = $voter->vote($tokenProphecy->reveal(), $meeting, ['MEETING_READ_VOTER']);

        self::assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testAttendeesOfMeetingAccess()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $securityProphecy = $this->prophesize(Security::class);

        $tokenProphecy = $this->prophesize(TokenInterface::class);
        $tokenProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($attendee = new People());

        $meeting = new Meeting();
        $creator = new People();
        $meeting->setCreatedBy($creator);
        $meeting->addAttendee($attendee);
        $meeting->setConfidential(true);

        $serviceLocatorProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());
        $securityProphecy->isGranted('FEATURE_MEETING_READ', $meeting)->shouldBeCalledTimes(1)->willReturn(false);
        $securityProphecy->isGranted('MOO_MOM')->shouldBeCalledTimes(1)->willReturn(false);

        $voter = new MeetingAccessVoter($serviceLocatorProphecy->reveal());

        $result = $voter->vote($tokenProphecy->reveal(), $meeting, ['MEETING_READ_VOTER']);

        self::assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testSuperUserAccess()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $securityProphecy = $this->prophesize(Security::class);

        $tokenProphecy = $this->prophesize(TokenInterface::class);
        $tokenProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($superUser = new People());

        $meeting = new Meeting();
        $creator = new People();
        $meeting->setCreatedBy($creator);
        $meeting->setConfidential(true);

        $serviceLocatorProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());
        $securityProphecy->isGranted('FEATURE_MEETING_READ', $meeting)->shouldBeCalledTimes(1)->willReturn(true);
        $securityProphecy->isGranted('MOO_MOM')->shouldNotBeCalled();

        $voter = new MeetingAccessVoter($serviceLocatorProphecy->reveal());

        $result = $voter->vote($tokenProphecy->reveal(), $meeting, ['MEETING_READ_VOTER']);

        self::assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testMOOAccess()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $securityProphecy = $this->prophesize(Security::class);

        $tokenProphecy = $this->prophesize(TokenInterface::class);
        $tokenProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($superUser = new People());

        $meeting = new Meeting();
        $creator = new People();
        $meeting->setCreatedBy($creator);
        $meeting->setConfidential(true);

        $serviceLocatorProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());
        $securityProphecy->isGranted('FEATURE_MEETING_READ', $meeting)->shouldBeCalledTimes(1)->willReturn(false);
        $securityProphecy->isGranted('MOO_MOM')->shouldBeCalledTimes(1)->willReturn(true);

        $voter = new MeetingAccessVoter($serviceLocatorProphecy->reveal());

        $result = $voter->vote($tokenProphecy->reveal(), $meeting, ['MEETING_READ_VOTER']);

        self::assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testPeopleNotAllowedMeetingAccess()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $subscriptionRepositoryMock = $this->getMockBuilder(SubscriptionRepository::class)->disableOriginalConstructor()->onlyMethods(['isFollowingResource'])->getMock();
        $securityProphecy = $this->prophesize(Security::class);

        $tokenProphecy = $this->prophesize(TokenInterface::class);
        $tokenProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($people = new People());

        $meeting = new Meeting();
        $creator = new People();
        $meeting->setCreatedBy($creator);
        $meeting->setConfidential(true);

        $serviceLocatorProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());
        $serviceLocatorProphecy->get(SubscriptionRepository::class)->shouldBeCalledTimes(1)->willReturn($subscriptionRepositoryMock);
        $securityProphecy->isGranted('FEATURE_MEETING_READ', $meeting)->shouldBeCalledTimes(1)->willReturn(false);
        $securityProphecy->isGranted('MOO_MOM')->shouldBeCalledTimes(1)->willReturn(false);

        $subscriptionRepositoryMock->expects($this->once())->method('isFollowingResource')->with($people, $meeting)->willReturn(false);

        $voter = new MeetingAccessVoter($serviceLocatorProphecy->reveal());

        $result = $voter->vote($tokenProphecy->reveal(), $meeting, ['MEETING_READ_VOTER']);

        self::assertSame(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testPeopleMeetingAccess()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $subscriptionRepositoryMock = $this->getMockBuilder(SubscriptionRepository::class)->disableOriginalConstructor()->onlyMethods(['isFollowingResource'])->getMock();
        $subscriptionRepositoryMock->expects($this->never())->method('isFollowingResource');

        $tokenProphecy = $this->prophesize(TokenInterface::class);
        $tokenProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($people = new People());

        $meeting = new Meeting();
        $meeting->setConfidential(false);

        $voter = new MeetingAccessVoter($serviceLocatorProphecy->reveal());

        $result = $voter->vote($tokenProphecy->reveal(), $meeting, ['MEETING_READ_VOTER']);

        self::assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }
}
