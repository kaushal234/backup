<?php

declare(strict_types=1);

namespace App\Tests\Security\Voter\MinutesOfMeeting;

use App\Entity\Directory\People;
use App\Entity\MinutesOfMeeting\Action;
use App\Security\Voter\MinutesOfMeeting\ActionWriteVoter;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ActionWriteVoterTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @dataProvider voterDataProvider
     */
    public function testVoterRestrictPeopleWhoCanWriteActions(Action $action, People $people, int $result)
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);

        $tokenProphecy = $this->prophesize(TokenInterface::class);
        $tokenProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($people);

        $voter = new ActionWriteVoter($serviceLocatorProphecy->reveal());

        self::assertSame($result, $voter->vote($tokenProphecy->reveal(), $action, ['ACTION_WRITE_VOTER']));
    }

    public function voterDataProvider()
    {
        $people = new People();
        $supervisor = new People();
        $director = new People();
        $supervisor->setSupervisor($director);
        $people->setSupervisor($supervisor);

        $action = new Action();
        $action->setAssignee($people);

        yield 'Assignee of the Action is the current user' => [$action, $people, VoterInterface::ACCESS_GRANTED];
        yield 'Current user is the supervisor of the assignee of the action' => [$action, $supervisor, VoterInterface::ACCESS_GRANTED];
        yield 'Current user is the supervisor of the supervisor of the assignee of the action' => [$action, $director, VoterInterface::ACCESS_GRANTED];

        $action = new Action();
        $action->setAssignee(new People());

        yield 'Current user is no one' => [$action, $people, VoterInterface::ACCESS_DENIED];
    }
}
