<?php

declare(strict_types=1);

namespace App\Tests\Security\Voter;

use App\Entity\Country;
use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Sales\Customer;
use App\Entity\Sales\CustomerType;
use App\Entity\Sales\MainSalesRepresentative;
use App\Entity\Sales\SalesArea;
use App\Entity\Sales\SalesForecast;
use App\Entity\Sales\SecondarySalesRepresentative;
use App\Repository\Common\SubscriptionRepository;
use App\Repository\Sales\SalesForecastRepository;
use App\Security\Voter\Sales\SalesForecast\SalesForecastAccessVoter;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SalesForecastAccessVoterTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @dataProvider voteContextProvider
     */
    public function testVoter(People $people, SalesForecast $sfr, $feature, int $vote)
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $securityProphecy = $this->prophesize(Security::class);
        $repositoryMock = $this->getMockBuilder(SalesForecastRepository::class)->disableOriginalConstructor()->onlyMethods(['getOriginalFactoryId'])->getMock();
        $subscriptionRepositoryMock = $this->getMockBuilder(SubscriptionRepository::class)->disableOriginalConstructor()->onlyMethods(['isFollowingResource'])->getMock();

        $features = [
            'FEATURE_SALES_FORECAST_VIEW_FULL',
            'MOO_SFR',
            'FEATURE_SALES_FORECAST_VIEW_ASM',
            'FEATURE_SALES_FORECAST_VIEW_CUSTOMER',
            'FEATURE_SALES_FORECAST_VIEW_SSO',
            'FEATURE_SALES_FORECAST_VIEW_FACTORY',
            'FEATURE_SALES_FORECAST_VIEW_MILITARY',
        ];

        $serviceLocatorProphecy->get(SubscriptionRepository::class)->shouldBeCalledTimes(1)->willReturn($subscriptionRepositoryMock);
        $subscriptionRepositoryMock->expects($this->once())->method('isFollowingResource')->with($people, $sfr)->willReturn(false);

        $serviceLocatorProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());

        foreach ($features as $f) {
            if ('FEATURE_SALES_FORECAST_VIEW_SSO' === $f) {
                $f = $f.'_'.$sfr->getSso()->getId();
            } elseif ('FEATURE_SALES_FORECAST_VIEW_FACTORY' === $f) {
                $serviceLocatorProphecy->get(SalesForecastRepository::class)->shouldBeCalledTimes(1)->willReturn($repositoryMock);
                $repositoryMock->expects($this->once())->method('getOriginalFactoryId')->with($sfr)->willReturn($sfr->getFactory()->getId());

                $f = $f.'_'.$sfr->getFactory()->getId();
            }

            $securityProphecy->isGranted($f)->shouldBeCalledTimes(1)->willReturn($f === $feature);

            if ($f === $feature && Voter::ACCESS_GRANTED === $vote) {
                break;
            }
        }

        $tokenProphecy = $this->prophesize(TokenInterface::class);
        $tokenProphecy->getUser()->willReturn($people);

        $voter = new SalesForecastAccessVoter($serviceLocatorProphecy->reveal());

        $result = $voter->vote($tokenProphecy->reveal(), $sfr, ['SALES_FORECAST_ACCESS_VOTER']);

        self::assertSame($vote, $result);
    }

    public function voteContextProvider()
    {
        yield 'User has full access' => [
            $this->createPeople(1, 2),
            $this->createSalesForecast(3, 4, 5, 6, 7),
            'FEATURE_SALES_FORECAST_VIEW_FULL',
            Voter::ACCESS_GRANTED,
        ];

        $asm = $this->createPeople(12, 50);

        yield 'User is the same than the ASM of the SFR' => [
            $asm,
            $this->createSalesForecast(12, 1, 1, 1, 1),
            'FEATURE_SALES_FORECAST_VIEW_ASM',
            Voter::ACCESS_GRANTED,
        ];

        $supervisor = $this->createPeople(24, 50);
        $sfr = $this->createSalesForecast(12, 1, 1, 1, 1);
        $sfr->getAsm()->setSupervisor($supervisor);

        yield 'User is the supervisor of the ASM of the SFR' => [
            $supervisor,
            $sfr,
            'FEATURE_SALES_FORECAST_VIEW_ASM',
            Voter::ACCESS_GRANTED,
        ];

        $supervisor = $this->createPeople(24, 50);
        $superSupervisor = $this->createPeople(36, 50);
        $supervisor->setSupervisor($superSupervisor);
        $sfr = $this->createSalesForecast(12, 1, 1, 1, 1);
        $sfr->getAsm()->setSupervisor($supervisor);

        yield 'User is the supervisor of the supervisor of the ASM of the SFR' => [
            $superSupervisor,
            $sfr,
            'FEATURE_SALES_FORECAST_VIEW_ASM',
            Voter::ACCESS_GRANTED,
        ];

        yield 'User is not the ASM of the SFR' => [
            $asm,
            $this->createSalesForecast(13, 1, 1, 1, 1),
            'FEATURE_SALES_FORECAST_VIEW_ASM',
            Voter::ACCESS_DENIED,
        ];

        yield 'User is the ASM of the buyer' => [
            $this->createPeople(1, 2),
            $this->createSalesForecast(3, 1, 5, 6, 7),
            'FEATURE_SALES_FORECAST_VIEW_CUSTOMER',
            Voter::ACCESS_GRANTED,
        ];

        yield 'User is the ASM of the end user' => [
            $this->createPeople(1, 2),
            $this->createSalesForecast(3, 4, 1, 6, 7),
            'FEATURE_SALES_FORECAST_VIEW_CUSTOMER',
            Voter::ACCESS_GRANTED,
        ];

        $sfr = $this->createSalesForecast(3, 99, 99, 6, 7);
        $buyerMainSalesRepresentative = new SecondarySalesRepresentative();
        $buyerMainSalesRepresentative->asm = $this->createPeople(1);
        $sfr->getBuyer()->addSecondarySalesRepresentative($buyerMainSalesRepresentative);

        yield 'User is a secondary ASM of the buyer' => [
            $this->createPeople(1, 2),
            $sfr,
            'FEATURE_SALES_FORECAST_VIEW_CUSTOMER',
            Voter::ACCESS_GRANTED,
        ];

        $sfr = $this->createSalesForecast(3, 99, 99, 6, 7);
        $buyerMainSalesRepresentative = new SecondarySalesRepresentative();
        $buyerMainSalesRepresentative->asm = $this->createPeople(1);
        $sfr->getEndUser()->addSecondarySalesRepresentative($buyerMainSalesRepresentative);

        yield 'User is a secondary ASM of the end user' => [
            $this->createPeople(1, 2),
            $sfr,
            'FEATURE_SALES_FORECAST_VIEW_CUSTOMER',
            Voter::ACCESS_GRANTED,
        ];

        yield 'User is not the ASM of buyer nor end user (but still granted)' => [
            $this->createPeople(1, 2),
            $this->createSalesForecast(3, 4, 5, 6, 7),
            'FEATURE_SALES_FORECAST_VIEW_CUSTOMER',
            Voter::ACCESS_DENIED,
        ];

        yield 'User is linked to the same SSO' => [
            $this->createPeople(1, 6),
            $this->createSalesForecast(3, 4, 5, 6, 7),
            'FEATURE_SALES_FORECAST_VIEW_SSO_6',
            Voter::ACCESS_GRANTED,
        ];

        yield 'User is linked to the same SSO but not granted' => [
            $this->createPeople(1, 6),
            $this->createSalesForecast(3, 4, 5, 6, 7),
            'NOPE',
            Voter::ACCESS_DENIED,
        ];

        yield 'User is granted but not on same SSO' => [
            $this->createPeople(1, 2),
            $this->createSalesForecast(3, 4, 5, 6, 7),
            'FEATURE_SALES_FORECAST_VIEW_SSO_99',
            Voter::ACCESS_DENIED,
        ];

        yield 'User is linked to the same Factory' => [
            $this->createPeople(1, 7),
            $this->createSalesForecast(3, 4, 5, 6, 7),
            'FEATURE_SALES_FORECAST_VIEW_FACTORY_7',
            Voter::ACCESS_GRANTED,
        ];

        yield 'User is linked to the same Factory but not granted' => [
            $this->createPeople(1, 7),
            $this->createSalesForecast(3, 4, 5, 6, 7),
            'NOPE',
            Voter::ACCESS_DENIED,
        ];

        yield 'User is granted but not on same factory' => [
            $this->createPeople(1, 2),
            $this->createSalesForecast(3, 4, 5, 6, 7),
            'FEATURE_SALES_FORECAST_VIEW_FACTORY_99',
            Voter::ACCESS_DENIED,
        ];

        yield 'User is granted for miliary' => [
            $this->createPeople(1, 2),
            $this->createSalesForecast(3, 4, 5, 6, 7, true),
            'FEATURE_SALES_FORECAST_VIEW_MILITARY',
            Voter::ACCESS_GRANTED,
        ];

        yield 'You know nothing, user' => [
            $this->createPeople(1, 2),
            $this->createSalesForecast(3, 4, 5, 6, 7),
            'NO_FEATURE',
            Voter::ACCESS_DENIED,
        ];

        $asm = $this->createPeople(1, 2);
        $sfr = $this->createSalesForecast(3, 4, 5, 6, 7);

        $salesArea = new SalesArea();
        $salesArea->setAsm($asm);

        $country = new Country();
        $country->addSalesArea($salesArea);

        $sfr->setCountry($country);

        yield 'User is ASM on the country of the SFR' => [
            $asm,
            $sfr,
            'FEATURE_SALES_FORECAST_VIEW_ASM',
            Voter::ACCESS_GRANTED,
        ];
    }

    private function createPeople(int $userId, ?int $locationId = null): People
    {
        $people = new People();

        if ($locationId) {
            $people->setBusinessUnit($this->createBusinessUnit($locationId));
        }

        $refl = new \ReflectionClass($people);

        /** @var \ReflectionClass $user */
        $user = $refl->getParentClass();

        $reflectionProperty = $user->getProperty('id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($people, $userId);

        return $people;
    }

    private function createBusinessUnit(int $locationId): BusinessUnit
    {
        $businessUnit = new BusinessUnit();
        $businessUnit->setLocation($this->createLocation($locationId));

        return $businessUnit;
    }

    private function createLocation(int $locationId): Location
    {
        $location = new Location();
        $location->setLegacyId($locationId);

        $refl = new \ReflectionClass($location);

        $reflectionProperty = $refl->getProperty('id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($location, $locationId);

        return $location;
    }

    private function createSalesForecast(int $asmId, int $buyerAsmId, int $userAsmId, int $ssoId, int $factoryId, bool $military = false): SalesForecast
    {
        $sfr = new SalesForecast();

        $customerType = (new CustomerType())->setName($military ? 'Military' : 'Pacific');

        $buyerMainSalesRepresentative = new MainSalesRepresentative();
        $buyerMainSalesRepresentative->asm = $this->createPeople($buyerAsmId);
        $endUserMainSalesRepresentative = new MainSalesRepresentative();
        $endUserMainSalesRepresentative->asm = $this->createPeople($userAsmId);

        $sfr
            ->setAsm($this->createPeople($asmId))
            ->setBuyer((new Customer())->setMainSalesRepresentative($buyerMainSalesRepresentative)->addCustomerType($customerType))
            ->setEndUser((new Customer())->setMainSalesRepresentative($endUserMainSalesRepresentative)->addCustomerType($customerType))
            ->setSso($this->createLocation($ssoId))
            ->setFactory($this->createLocation($factoryId))
        ;

        return $sfr;
    }
}
