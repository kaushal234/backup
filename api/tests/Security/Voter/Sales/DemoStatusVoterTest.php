<?php

declare(strict_types=1);

namespace App\Tests\Security\Voter\Sales;

use App\Entity\Directory\People;
use App\Entity\Sales\Demo;
use App\Security\Voter\Sales\Demo\DemoStatusVoter;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class DemoStatusVoterTest extends TestCase
{
    use ProphecyTrait;

    public function testMOOCanChangeDemoStatus()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);

        $people = new People();
        $demo = new Demo();
        $demo->setAsm(new People());

        $tokenProphecy = $this->prophesize(TokenInterface::class);
        $tokenProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($people);

        $securityProphecy = $this->prophesize(Security::class);
        $serviceLocatorProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());

        $securityProphecy->isGranted('FEATURE_DEMO_STATUS')->shouldBeCalledTimes(1)->willReturn(false);
        $securityProphecy->isGranted('FEATURE_DEMO_ADMIN')->shouldBeCalledTimes(1)->willReturn(false);
        $securityProphecy->isGranted('MOO_DEMO')->shouldBeCalledTimes(1)->willReturn(true);

        $voter = new DemoStatusVoter($serviceLocatorProphecy->reveal());

        self::assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote($tokenProphecy->reveal(), $demo, ['DEMO_STATUS_VOTER']));
    }

    public function testStatusDemoCanBeChangeByDemoAdmin()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);

        $people = new People();
        $demo = new Demo();
        $demo->setAsm(new People());

        $tokenProphecy = $this->prophesize(TokenInterface::class);
        $tokenProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($people);

        $securityProphecy = $this->prophesize(Security::class);
        $serviceLocatorProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());

        $securityProphecy->isGranted('FEATURE_DEMO_STATUS')->shouldBeCalledTimes(1)->willReturn(false);
        $securityProphecy->isGranted('FEATURE_DEMO_ADMIN')->shouldBeCalledTimes(1)->willReturn(true);
        $securityProphecy->isGranted('MOO_DEMO')->shouldNotBeCalled();

        $voter = new DemoStatusVoter($serviceLocatorProphecy->reveal());

        self::assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote($tokenProphecy->reveal(), $demo, ['DEMO_STATUS_VOTER']));
    }

    public function testStatusDemoCanBeChangeByAuthorizedPeople()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);

        $people = new People();
        $demo = new Demo();
        $demo->setAsm(new People());

        $tokenProphecy = $this->prophesize(TokenInterface::class);
        $tokenProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($people);

        $securityProphecy = $this->prophesize(Security::class);
        $serviceLocatorProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());

        $securityProphecy->isGranted('FEATURE_DEMO_STATUS')->shouldBeCalledTimes(1)->willReturn(true);
        $securityProphecy->isGranted('FEATURE_DEMO_ADMIN')->shouldNotBeCalled();
        $securityProphecy->isGranted('MOO_DEMO')->shouldNotBeCalled();

        $voter = new DemoStatusVoter($serviceLocatorProphecy->reveal());

        self::assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote($tokenProphecy->reveal(), $demo, ['DEMO_STATUS_VOTER']));
    }

    public function testAsmOfDemoCanChangeStatus()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);

        $asm = new People();

        $demo = new Demo();
        $demo->setAsm($asm);

        $tokenProphecy = $this->prophesize(TokenInterface::class);
        $tokenProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($asm);

        $voter = new DemoStatusVoter($serviceLocatorProphecy->reveal());

        self::assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote($tokenProphecy->reveal(), $demo, ['DEMO_STATUS_VOTER']));
    }
}
