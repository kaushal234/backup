<?php

declare(strict_types=1);

namespace App\Tests\Security\Voter\LeadTime;

use App\Entity\Acl;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Group;
use App\Entity\Manufacturing\LeadTime;
use App\Security\Voter\Manufacturing\LeadTimeVoter;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class LeadTimeVoterTest extends TestCase
{
    use ProphecyTrait;

    public function testAdminCanAdministrateLeadTime()
    {
        $people = new People();
        $leadTime = new LeadTime();
        $leadTime->factory = new Location();

        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $securityProphecy = $this->prophesize(Security::class);

        $tokenProphecy = $this->prophesize(TokenInterface::class);
        $tokenProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($people);

        $serviceLocatorProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());
        $securityProphecy->isGranted('MOO_SLT')->shouldBeCalledTimes(1)->willReturn(false);
        $securityProphecy->isGranted('FEATURE_LEAD_TIME_WRITE_ADMIN')->shouldBeCalledTimes(1)->willReturn(true);

        $voter = new LeadTimeVoter($serviceLocatorProphecy->reveal());

        self::assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote($tokenProphecy->reveal(), $leadTime, ['LEAD_TIME_WRITE_VOTER']));
    }

    public function testMOOCanAdministrateLeadTime()
    {
        $people = new People();
        $leadTime = new LeadTime();
        $leadTime->factory = new Location();

        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $securityProphecy = $this->prophesize(Security::class);

        $tokenProphecy = $this->prophesize(TokenInterface::class);
        $tokenProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($people);

        $serviceLocatorProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());
        $securityProphecy->isGranted('MOO_SLT')->shouldBeCalledTimes(1)->willReturn(true);
        $securityProphecy->isGranted('FEATURE_LEAD_TIME_WRITE_ADMIN')->shouldNotbeCalled();

        $voter = new LeadTimeVoter($serviceLocatorProphecy->reveal());

        self::assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote($tokenProphecy->reveal(), $leadTime, ['LEAD_TIME_WRITE_VOTER']));
    }

    public function testPsmWithRightLocationCanAdministrateLeadTime()
    {
        $location = new Location();
        $people = new People();
        $people->addAcl((new Acl())->setGroup((new Group())->setName('ROLE_PSM'))->setLocation($location));
        $leadTime = new LeadTime();
        $leadTime->factory = $location;

        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $securityProphecy = $this->prophesize(Security::class);

        $tokenProphecy = $this->prophesize(TokenInterface::class);
        $tokenProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($people);

        $serviceLocatorProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());
        $securityProphecy->isGranted('MOO_SLT')->shouldNotBeCalled();
        $securityProphecy->isGranted('FEATURE_LEAD_TIME_WRITE_ADMIN')->shouldNotBeCalled();

        $voter = new LeadTimeVoter($serviceLocatorProphecy->reveal());

        self::assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote($tokenProphecy->reveal(), $leadTime, ['LEAD_TIME_WRITE_VOTER']));
    }

    public function testPsmWithNotRightLocationCanNotAdministrateLeadTime()
    {
        $location = new Location();
        $otherLocation = new Location();
        $people = new People();
        $people->addAcl((new Acl())->setGroup((new Group())->setName('ROLE_PSM'))->setLocation($location));
        $leadTime = new LeadTime();
        $leadTime->factory = $otherLocation;

        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $securityProphecy = $this->prophesize(Security::class);

        $tokenProphecy = $this->prophesize(TokenInterface::class);
        $tokenProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($people);

        $serviceLocatorProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());
        $securityProphecy->isGranted('MOO_SLT')->shouldBeCalledTimes(1)->willReturn(false);
        $securityProphecy->isGranted('FEATURE_LEAD_TIME_WRITE_ADMIN')->shouldBeCalledTimes(1)->willReturn(false);

        $voter = new LeadTimeVoter($serviceLocatorProphecy->reveal());

        self::assertSame(VoterInterface::ACCESS_DENIED, $voter->vote($tokenProphecy->reveal(), $leadTime, ['LEAD_TIME_WRITE_VOTER']));
    }
}
