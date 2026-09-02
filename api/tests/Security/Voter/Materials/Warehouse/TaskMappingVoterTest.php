<?php

declare(strict_types=1);

namespace App\Tests\Security\Voter\Materials\Warehouse;

use App\Entity\Acl;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Group;
use App\Entity\Materials\Warehouse\TasksMapping;
use App\Entity\User;
use App\Security\Voter\Materials\Warehouse\TasksMappingVoter;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class TaskMappingVoterTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @dataProvider earlyDeniedDataProvider
     */
    public function testVoterEarlyDenied(object $user, ?object $subject = null)
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);

        $tokenProphecy = $this->prophesize(TokenInterface::class);
        $tokenProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($user);

        $voter = new TasksMappingVoter($serviceLocatorProphecy->reveal());

        self::assertSame(Voter::ACCESS_DENIED, $voter->vote($tokenProphecy->reveal(), $subject, ['TASKS_MAPPING_WRITE_VOTER']));
    }

    public function earlyDeniedDataProvider(): \Generator
    {
        yield 'not a People' => [new User()];
        yield 'not a task Mapping' => [new People(), new \stdClass()];
    }

    /**
     * @dataProvider voterDataProvider
     */
    public function testVoterDeniedOnGroups(int $expected, object $user, ?object $subject = null, bool $isMOO = false)
    {
        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->isGranted('MOO_WHSE')->shouldBeCalledTimes(1)->willReturn($isMOO);

        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $serviceLocatorProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());

        $tokenProphecy = $this->prophesize(TokenInterface::class);
        $tokenProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($user);
        $voter = new TasksMappingVoter($serviceLocatorProphecy->reveal());

        self::assertSame($expected, $voter->vote($tokenProphecy->reveal(), $subject, ['TASKS_MAPPING_WRITE_VOTER']));
    }

    public function voterDataProvider(): \Generator
    {
        $location = new Location();
        yield 'no ACLs' => [VoterInterface::ACCESS_DENIED, new People()];

        yield 'no mapping and MOO' => [VoterInterface::ACCESS_GRANTED, new People(), null, true];
        yield 'a mapping and MOO' => [Voter::ACCESS_GRANTED, new People(), new TasksMapping(), true];

        foreach (['ROLE_MLM', 'ROLE_WS'] as $role) {
            $user = (new People())->addAcl((new Acl())->setGroup((new Group())->setName($role)));
            yield "no mapping and $role" => [Voter::ACCESS_GRANTED, $user];

            yield "a mapping and $role not granted on the location" => [Voter::ACCESS_DENIED, $user, (new TasksMapping())->setLocation($location)];

            $user = (new People())->addAcl((new Acl())->setGroup((new Group())->setName($role))->setLocation($location));
            yield "a mapping and a $role granted on the location" => [Voter::ACCESS_GRANTED, $user, (new TasksMapping())->setLocation($location)];
        }
    }
}
