<?php

declare(strict_types=1);

namespace App\Tests\Security\Voter\MinutesOfMeeting;

use App\Entity\Directory\People;
use App\Entity\MinutesOfMeeting\Meeting;
use App\Security\Voter\MinutesOfMeeting\MeetingWriteVoter;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class MeetingWriteVoterTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @dataProvider voterDataProvider
     */
    public function testVoterRestrictPeopleWhoCanWriteMeetings(Meeting $meeting, People $people, int $result)
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);

        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->isGranted('FEATURE_MEETING_WRITE')->shouldBeCalledTimes(1)->willReturn(false);
        $securityProphecy->isGranted('MOO_MOM')->shouldBeCalledTimes(1)->willReturn(false);

        $tokenProphecy = $this->prophesize(TokenInterface::class);
        $tokenProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($people);

        $serviceLocatorProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());
        $voter = new MeetingWriteVoter($serviceLocatorProphecy->reveal());

        self::assertSame($result, $voter->vote($tokenProphecy->reveal(), $meeting, ['MEETING_WRITE_VOTER']));
    }

    public function voterDataProvider()
    {
        $people = new People();
        $supervisor = new People();
        $director = new People();
        $supervisor->setSupervisor($director);
        $people->setSupervisor($supervisor);

        $meeting = new Meeting();
        $meeting->setCreatedBy($people);

        yield 'Meeting poster is the current user' => [$meeting, $people, VoterInterface::ACCESS_GRANTED];
        yield 'Current user is the supervisor of the poster of the meeting' => [$meeting, $supervisor, VoterInterface::ACCESS_GRANTED];
        yield 'Current user is the supervisor of the supervisor of the poster of the meeting' => [$meeting, $director, VoterInterface::ACCESS_GRANTED];

        $meeting = new Meeting();
        $meeting->setCreatedBy(new People());

        yield 'Current user is no one' => [$meeting, $people, VoterInterface::ACCESS_DENIED];
    }

    public function testSuperUserCanWriteMeeting()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $superUser = new People();
        $people = new People();

        $meeting = new Meeting();
        $meeting->setCreatedBy($people);

        $tokenProphecy = $this->prophesize(TokenInterface::class);
        $tokenProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($superUser);
        $securityProphecy = $this->prophesize(Security::class);
        $serviceLocatorProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());
        $securityProphecy->isGranted('FEATURE_MEETING_WRITE')->shouldBeCalledTimes(1)->willReturn(true);

        $voter = new MeetingWriteVoter($serviceLocatorProphecy->reveal());

        self::assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote($tokenProphecy->reveal(), $meeting, ['MEETING_WRITE_VOTER']));
    }

    public function testMOOCanWriteMeeting()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $moo = new People();
        $people = new People();

        $meeting = new Meeting();
        $meeting->setCreatedBy($people);

        $tokenProphecy = $this->prophesize(TokenInterface::class);
        $tokenProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($moo);
        $securityProphecy = $this->prophesize(Security::class);
        $serviceLocatorProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());
        $securityProphecy->isGranted('FEATURE_MEETING_WRITE')->shouldBeCalledTimes(1)->willReturn(false);
        $securityProphecy->isGranted('MOO_MOM')->shouldBeCalledTimes(1)->willReturn(true);

        $voter = new MeetingWriteVoter($serviceLocatorProphecy->reveal());

        self::assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote($tokenProphecy->reveal(), $meeting, ['MEETING_WRITE_VOTER']));
    }

    public function testAttendeesCanWriteMeeting()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $creator = new People();
        $attendee = new People();

        $meeting = new Meeting();
        $meeting->setCreatedBy($creator)->addAttendee($attendee);

        $tokenProphecy = $this->prophesize(TokenInterface::class);
        $tokenProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($attendee);
        $securityProphecy = $this->prophesize(Security::class);
        $serviceLocatorProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());
        $securityProphecy->isGranted('FEATURE_MEETING_WRITE')->shouldBeCalledTimes(1)->willReturn(false);
        $securityProphecy->isGranted('MOO_MOM')->shouldBeCalledTimes(1)->willReturn(false);

        $voter = new MeetingWriteVoter($serviceLocatorProphecy->reveal());

        self::assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote($tokenProphecy->reveal(), $meeting, ['MEETING_WRITE_VOTER']));
    }
}
