<?php

declare(strict_types=1);

namespace App\Tests\Security\Voter;

use App\Entity\Acl;
use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Group;
use App\Entity\Sales\ExtranetUser;
use App\Security\Voter\Directory\People\PeoplePartialUpdateVoter;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PeoplePartialUpdateVoterTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @dataProvider provideVoteContext
     */
    public function testVoter($currentUser, $subject, $vote)
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $tokenProphecy = $this->prophesize(TokenInterface::class);
        $tokenProphecy->getUser()->willReturn($currentUser);

        $securityProphecy = $this->prophesize(Security::class);

        if ($currentUser instanceof People && null !== ($businessUnit = $currentUser->getBusinessUnit())) {
            switch ($businessUnit->getName()) {
                case 'MPE_BU':
                    $securityProphecy->isGranted('role_MPE', $businessUnit->getLocation())->shouldBeCalledTimes(1)->willReturn(true);
                    break;
                case 'PS_BU':
                    $securityProphecy->isGranted('role_MPE', $businessUnit->getLocation())->shouldBeCalledTimes(1)->willReturn(false);
                    $securityProphecy->isGranted('ROLE_PS', $businessUnit->getLocation())->shouldBeCalledTimes(1)->willReturn(true);
                    break;
                case 'HR_BU':
                    $securityProphecy->isGranted('role_MPE', $businessUnit->getLocation())->shouldBeCalledTimes(1)->willReturn(false);
                    $securityProphecy->isGranted('ROLE_PS', $businessUnit->getLocation())->shouldBeCalledTimes(1)->willReturn(false);
                    break;
            }
            $serviceLocatorProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());
        }

        $voter = new PeoplePartialUpdateVoter($serviceLocatorProphecy->reveal());

        $result = $voter->vote($tokenProphecy->reveal(), $subject, ['PEOPLE_PARTIAL_UPDATE_VOTER']);

        self::assertSame($vote, $result);
    }

    public function provideVoteContext(): \Generator
    {
        $activePeople = new People();
        $activePeople->setHidden(false)->setDisabled(false);

        yield 'Current User is not a People' => [new ExtranetUser(), clone $activePeople, Voter::ACCESS_DENIED];
        yield 'Subject is not a People' => [clone $activePeople, new ExtranetUser(), Voter::ACCESS_ABSTAIN];

        $aclAuthIntranetPeople = clone $activePeople;

        $refl = new \ReflectionClass($aclAuthIntranetPeople);

        /** @var \ReflectionClass $user */
        $user = $refl->getParentClass();

        $reflectionProperty = $user->getProperty('acls');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($aclAuthIntranetPeople, new ArrayCollection([(new Acl())->setGroup((new Group())->setName('ACL_AUTH_INTRANET'))]));

        yield 'Subject is granted ACL_AUTH_INTRANET' => [clone $activePeople, clone $aclAuthIntranetPeople, Voter::ACCESS_DENIED];

        $hiddenPeople = (new People())->setHidden(true)->setDisabled(false);

        yield 'Subject is hidden' => [clone $activePeople, clone $hiddenPeople, Voter::ACCESS_DENIED];

        $disabledPeople = (new People())->setHidden(false)->setDisabled(true);

        yield 'Subject is disabled' => [clone $activePeople, clone $disabledPeople, Voter::ACCESS_DENIED];

        $supervisor = clone $activePeople;
        $subject = clone $activePeople;
        $subject->setSupervisor($supervisor);

        yield 'Current user is the supervisor of the subject' => [$supervisor, $subject, Voter::ACCESS_GRANTED];

        $supervisor = clone $activePeople;
        $supervisorOfSupervisor = clone $activePeople;
        $supervisor->setSupervisor($supervisorOfSupervisor);
        $subject = clone $activePeople;
        $subject->setSupervisor($supervisor);

        yield 'Current user is the supervisor of the supervisor of the subject' => [$supervisorOfSupervisor, $subject, Voter::ACCESS_GRANTED];

        $userWithLocation = (clone $activePeople)->setBusinessUnit((new BusinessUnit())->setLocation(new Location())->setName('MPE_BU'));

        yield 'Current user is the MPE of the same location than the subject' => [$userWithLocation, clone $activePeople, Voter::ACCESS_GRANTED];

        $userWithLocation = (clone $activePeople)->setBusinessUnit((new BusinessUnit())->setLocation(new Location())->setName('PS_BU'));

        yield 'Current user is a production supervisor of the same location than the subject' => [$userWithLocation, clone $activePeople, Voter::ACCESS_GRANTED];

        $hrLocation = new Location();
        $userWithLocation = (clone $activePeople)->setBusinessUnit((new BusinessUnit())->setLocation($hrLocation)->setName('HR_BU'));
        $acl = new Acl();
        $acl
            ->setGroup((new Group())->setName('GG_HR'))
            ->setLocation($hrLocation)
        ;
        $userWithLocation->addAcl($acl);

        yield 'Current user is a HR employee of the same location than the subject' => [$userWithLocation, clone $activePeople, Voter::ACCESS_GRANTED];
    }
}
