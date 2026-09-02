<?php

declare(strict_types=1);

namespace App\Tests\Security\Voter\Sales;

use App\Entity\Directory\People;
use App\Entity\Sales\Demo;
use App\Security\Voter\Sales\Demo\DemoEditVoter;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class DemoEditVoterTest extends TestCase
{
    use ProphecyTrait;

    public function testAdminUserCanEditDemo()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);

        $people = new People();
        $demo = new Demo();

        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->isGranted('DEMO_ADMIN_EDIT_VOTER', $demo)->shouldBeCalledTimes(1)->willReturn(true);
        $securityProphecy->isGranted('MOO_DEMO')->shouldNotBeCalled();
        $securityProphecy->isGranted('FEATURE_DEMO_EDIT')->shouldNotBeCalled();

        $tokenProphecy = $this->prophesize(TokenInterface::class);
        $tokenProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($people);

        $serviceLocatorProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());
        $voter = new DemoEditVoter($serviceLocatorProphecy->reveal());

        self::assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote($tokenProphecy->reveal(), $demo, ['DEMO_EDIT_VOTER']));
    }

    public function testMOOCanEditDemo()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);

        $people = new People();
        $demo = new Demo();

        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->isGranted('DEMO_ADMIN_EDIT_VOTER', $demo)->shouldBeCalledTimes(1)->willReturn(false);
        $securityProphecy->isGranted('MOO_DEMO')->shouldBeCalledTimes(1)->willReturn(true);
        $securityProphecy->isGranted('FEATURE_DEMO_EDIT')->shouldNotBeCalled();

        $tokenProphecy = $this->prophesize(TokenInterface::class);
        $tokenProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($people);

        $serviceLocatorProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());
        $voter = new DemoEditVoter($serviceLocatorProphecy->reveal());

        self::assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote($tokenProphecy->reveal(), $demo, ['DEMO_EDIT_VOTER']));
    }

    public function testDemoCanBeEditedByAStandardUserIfNotClosed()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);

        $people = new People();
        $demo = new Demo();

        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->isGranted('DEMO_ADMIN_EDIT_VOTER', $demo)->shouldBeCalledTimes(1)->willReturn(false);
        $securityProphecy->isGranted('MOO_DEMO')->shouldBeCalledTimes(1)->willReturn(false);
        $securityProphecy->isGranted('FEATURE_DEMO_EDIT')->shouldBeCalledTimes(1)->willReturn(true);

        $tokenProphecy = $this->prophesize(TokenInterface::class);
        $tokenProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($people);

        $serviceLocatorProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());
        $voter = new DemoEditVoter($serviceLocatorProphecy->reveal());

        self::assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote($tokenProphecy->reveal(), $demo, ['DEMO_EDIT_VOTER']));
    }

    public function testStandardUserCannotEditClosedDemo()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);

        $people = new People();
        $demo = new Demo();
        $demo->setStatus('REJECTED');

        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->isGranted('DEMO_ADMIN_EDIT_VOTER', $demo)->shouldBeCalledTimes(1)->willReturn(false);
        $securityProphecy->isGranted('MOO_DEMO')->shouldBeCalledTimes(1)->willReturn(false);
        $securityProphecy->isGranted('FEATURE_DEMO_EDIT')->shouldNotBeCalled();

        $tokenProphecy = $this->prophesize(TokenInterface::class);
        $tokenProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($people);

        $serviceLocatorProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());
        $voter = new DemoEditVoter($serviceLocatorProphecy->reveal());

        self::assertSame(VoterInterface::ACCESS_DENIED, $voter->vote($tokenProphecy->reveal(), $demo, ['DEMO_EDIT_VOTER']));
    }
}
