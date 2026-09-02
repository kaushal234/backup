<?php

declare(strict_types=1);

namespace App\Tests\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Doctrine\ORM\Extension\SalesForecastExtension;
use App\Entity\Common\Subscription;
use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Directory\Region;
use App\Entity\Sales\CustomerType;
use App\Entity\Sales\SalesForecast;
use App\Repository\Directory\PeopleRepository;
use App\Repository\FeatureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Query\Expr\Func;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class SalesForecastExtensionTest extends TestCase
{
    use ProphecyTrait;
    /**
     * @var int
     */
    final public const USER_ID = 12;
    /**
     * @var int
     */
    final public const BU_ID = 69;
    /**
     * @var int
     */
    final public const REGION_ID = 99;

    public function testExtensionOnlyAppliesToSalesForecasts()
    {
        $emProphecy = $this->prophesize(EntityManagerInterface::class);
        $emProphecy->getExpressionBuilder()->willReturn(new Expr());
        $queryBuilder = new QueryBuilder($emProphecy->reveal());

        $queryBuilder->from(SalesForecast::class, 'o');

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->get(Security::class)->shouldNotBeCalled();
        $containerProphecy->get(PeopleRepository::class)->shouldNotBeCalled();
        $containerProphecy->get(FeatureRepository::class)->shouldNotBeCalled();

        $extension = new SalesForecastExtension($containerProphecy->reveal());

        $extension->applyToCollection($queryBuilder, new QueryNameGenerator(), \stdClass::class, new GetCollection());

        self::assertNull($queryBuilder->getDQLPart('where'));
    }

    public function testExtensionOnlyAppliesToRouteGet()
    {
        $emProphecy = $this->prophesize(EntityManagerInterface::class);
        $emProphecy->getExpressionBuilder()->willReturn(new Expr());
        $queryBuilder = new QueryBuilder($emProphecy->reveal());

        $queryBuilder->from(SalesForecast::class, 'o');

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->get(Security::class)->shouldNotBeCalled();
        $containerProphecy->get(PeopleRepository::class)->shouldNotBeCalled();
        $containerProphecy->get(FeatureRepository::class)->shouldNotBeCalled();

        $extension = new SalesForecastExtension($containerProphecy->reveal());

        $extension->applyToCollection($queryBuilder, new QueryNameGenerator(), SalesForecast::class, new Get('toute_la_sainte_journée'));

        self::assertNull($queryBuilder->getDQLPart('where'));
    }

    public function testFilterBlockAllResultsByDefault()
    {
        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($this->getUser());
        $securityProphecy->isGranted(Argument::any())->willReturn(false);

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());
        $containerProphecy->get(PeopleRepository::class)->shouldNotBeCalled();
        $containerProphecy->get(FeatureRepository::class)->shouldNotBeCalled();

        $queryBuilder = $this->applyExtension($containerProphecy->reveal());

        self::assertNotEmpty(array_filter($queryBuilder->getDQLPart('join')['o'], static fn (Join $join) => Subscription::class === $join->getJoin() && "CONCAT('/sales/sales_forecasts/', o.id) = subscription_a1.resource" === $join->getCondition()));
        self::assertSame('subscription_a1.user = :user', (string) $queryBuilder->getDQLPart('where'));
    }

    public function testFilterHasNoEffectForFullView()
    {
        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($this->getUser());
        $securityProphecy->isGranted('FEATURE_SALES_FORECAST_VIEW_FULL')->shouldBeCalledTimes(1)->willReturn(true);

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());
        $containerProphecy->get(PeopleRepository::class)->shouldNotBeCalled();
        $containerProphecy->get(FeatureRepository::class)->shouldNotBeCalled();

        $queryBuilder = $this->applyExtension($containerProphecy->reveal());

        $where = $queryBuilder->getDQLPart('where');

        self::assertNull($where);
    }

    public function testFilterRestrictsResultsForASM()
    {
        $security = $this->getSecurity(false, false, true, false, false, false);

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($security);
        $containerProphecy->get(PeopleRepository::class)->shouldNotBeCalled();
        $containerProphecy->get(FeatureRepository::class)->shouldNotBeCalled();

        $queryBuilder = $this->applyExtension($containerProphecy->reveal());

        /** @var Andx $where */
        $where = $queryBuilder->getDQLPart('where');
        self::assertInstanceOf(Andx::class, $where);

        $andParts = $where->getParts();
        self::assertCount(1, $andParts);
        /** @var Orx $orPart */
        $orPart = $andParts[0];
        self::assertInstanceOf(Orx::class, $orPart);

        $comparisonParts = $orPart->getParts();
        self::assertCount(3, $comparisonParts);

        $comparison1 = $comparisonParts[0];
        self::assertInstanceOf(Comparison::class, $comparison1);
        self::assertSame('o.asm = '.self::USER_ID, (string) $comparison1);

        $comparison2 = $comparisonParts[1];
        self::assertInstanceOf(Comparison::class, $comparison2);
        self::assertSame('asm_a1.supervisor = '.self::USER_ID, (string) $comparison2);

        $comparison3 = $comparisonParts[2];
        self::assertInstanceOf(Comparison::class, $comparison3);
        self::assertSame('supervisor_a2.supervisor = '.self::USER_ID, (string) $comparison3);
    }

    public function testFilterRestrictsResultsForCustomers()
    {
        $user = $this->getUser();
        $security = $this->getSecurity(false, false, false, true, false, false, false, $user);

        $peopleRepository = $this->getMockBuilder(PeopleRepository::class)->disableOriginalConstructor()->onlyMethods(['getSubordinates'])->getMock();
        $subordinate = (new People())->setSupervisor($user);
        $peopleRepository->expects($this->once())->method('getSubordinates')->with($user, 3)->willReturn([$user, $subordinate]);

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($security);
        $containerProphecy->get(PeopleRepository::class)->shouldBeCalledTimes(1)->willReturn($peopleRepository);
        $containerProphecy->get(FeatureRepository::class)->shouldNotBeCalled();

        $queryBuilder = $this->applyExtension($containerProphecy->reveal());

        /** @var Andx $where */
        $where = $queryBuilder->getDQLPart('where');
        self::assertInstanceOf(Andx::class, $where);

        $andParts = $where->getParts();

        self::assertCount(1, $andParts);
        /** @var Orx $orPart */
        $orPart = $andParts[0];
        self::assertInstanceOf(Orx::class, $orPart);

        $orParts = $orPart->getParts();
        self::assertCount(12, $orParts);

        self::assertInstanceOf(Func::class, $orParts[0]);
        self::assertSame('buyerMainSalesRepresentative_a3.asm IN(:hierarchy)', (string) $orParts[0]);

        self::assertInstanceOf(Func::class, $orParts[1]);
        self::assertSame('endUserMainSalesRepresentative_a4.asm IN(:hierarchy)', (string) $orParts[1]);

        self::assertInstanceOf(Func::class, $orParts[2]);
        self::assertSame('buyerSecondarySalesRepresentatives_a5.asm IN(:hierarchy)', (string) $orParts[2]);

        self::assertInstanceOf(Func::class, $orParts[3]);
        self::assertSame('endUserSecondarySalesRepresentatives_a6.asm IN(:hierarchy)', (string) $orParts[3]);

        self::assertInstanceOf(Func::class, $orParts[4]);
        self::assertSame('buyerMainSalesRepresentative_a9.asm IN(:hierarchy)', (string) $orParts[4]);

        self::assertInstanceOf(Func::class, $orParts[5]);
        self::assertSame('endUserMainSalesRepresentative_a10.asm IN(:hierarchy)', (string) $orParts[5]);

        self::assertInstanceOf(Func::class, $orParts[6]);
        self::assertSame('buyerSecondarySalesRepresentatives_a11.asm IN(:hierarchy)', (string) $orParts[6]);

        self::assertInstanceOf(Func::class, $orParts[7]);
        self::assertSame('endUserSecondarySalesRepresentatives_a12.asm IN(:hierarchy)', (string) $orParts[7]);

        self::assertInstanceOf(Func::class, $orParts[8]);
        self::assertSame('buyerMainSalesRepresentative_a15.asm IN(:hierarchy)', (string) $orParts[8]);

        self::assertInstanceOf(Func::class, $orParts[9]);
        self::assertSame('endUserMainSalesRepresentative_a16.asm IN(:hierarchy)', (string) $orParts[9]);

        self::assertInstanceOf(Func::class, $orParts[10]);
        self::assertSame('buyerSecondarySalesRepresentatives_a17.asm IN(:hierarchy)', (string) $orParts[10]);

        self::assertInstanceOf(Func::class, $orParts[11]);
        self::assertSame('endUserSecondarySalesRepresentatives_a18.asm IN(:hierarchy)', (string) $orParts[11]);
    }

    public function testFilterRestrictsResultsForSSO()
    {
        $security = $this->getSecurity(false, false, false, false, true, false);

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($security);
        $containerProphecy->get(PeopleRepository::class)->shouldNotBeCalled();
        $featureRepositoryMock = $this->getMockBuilder(FeatureRepository::class)->disableOriginalConstructor()->onlyMethods(['loadFeaturesByPeople'])->getMock();

        $containerProphecy->get(FeatureRepository::class)->shouldBeCalledOnce()->willReturn($featureRepositoryMock);

        $featureRepositoryMock->expects($this->once())->method('loadFeaturesByPeople')->with($this->callback(static fn ($people) => $people instanceof People))->willReturn([
            ['name' => 'FEATURE_SALES_FORECAST_VIEW_SSO', 'location_id' => 12],
            ['name' => 'FEATURE_SALES_FORECAST_VIEW_SSO', 'location_id' => 44],
            ['name' => 'FEATURE_SALES_FORECAST_VIEW_FACTORY', 'location_id' => 12],
            ['name' => 'FEATURE_SALES_FORECAST_VIEW_FACTORY', 'location_id' => 42],
            ['name' => 'FEATURE_SALES_FORECAST_VIEW_FACTORY', 'location_id' => 5],
        ]);

        $queryBuilder = $this->applyExtension($containerProphecy->reveal());

        /** @var Andx $where */
        $where = $queryBuilder->getDQLPart('where');
        self::assertInstanceOf(Andx::class, $where);

        $andParts = $where->getParts();

        self::assertCount(1, $andParts);
        /** @var Orx $orPart */
        $orPart = $andParts[0];
        self::assertInstanceOf(Orx::class, $orPart);

        $orParts = $orPart->getParts();
        self::assertCount(1, $orParts);

        self::assertSame('o.sso IN(:sso_ids)', (string) $orParts[0]);
        self::assertSame([12, 44], $queryBuilder->getParameter('sso_ids')->getValue());
    }

    public function testFilterRestrictsResultsForFactory()
    {
        $security = $this->getSecurity(false, false, false, false, false, true);

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($security);
        $containerProphecy->get(PeopleRepository::class)->shouldNotBeCalled();
        $featureRepositoryMock = $this->getMockBuilder(FeatureRepository::class)->disableOriginalConstructor()->onlyMethods(['loadFeaturesByPeople'])->getMock();
        $featureRepositoryMock->expects($this->once())->method('loadFeaturesByPeople')->with($this->callback(static fn ($people) => $people instanceof People))->willReturn([
            ['name' => 'FEATURE_SALES_FORECAST_VIEW_SSO', 'location_id' => 12],
            ['name' => 'FEATURE_SALES_FORECAST_VIEW_SSO', 'location_id' => 44],
            ['name' => 'FEATURE_SALES_FORECAST_VIEW_FACTORY', 'location_id' => 12],
            ['name' => 'FEATURE_SALES_FORECAST_VIEW_FACTORY', 'location_id' => 42],
            ['name' => 'FEATURE_SALES_FORECAST_VIEW_FACTORY', 'location_id' => 5],
        ]);
        $containerProphecy->get(FeatureRepository::class)->shouldBeCalledOnce()->willReturn($featureRepositoryMock);

        $queryBuilder = $this->applyExtension($containerProphecy->reveal());

        /** @var Andx $where */
        $where = $queryBuilder->getDQLPart('where');
        self::assertInstanceOf(Andx::class, $where);

        $andParts = $where->getParts();

        self::assertCount(1, $andParts);
        /** @var Orx $orPart */
        $orPart = $andParts[0];
        self::assertInstanceOf(Orx::class, $orPart);

        $orParts = $orPart->getParts();
        self::assertCount(1, $orParts);

        self::assertSame('o.factory IN(:factory_ids)', (string) $orParts[0]);
        self::assertSame([12, 42, 5], $queryBuilder->getParameter('factory_ids')->getValue());
    }

    public function testFilterRestrictsResultsForMilitary()
    {
        $security = $this->getSecurity(false, false, false, false, false, false, true);

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($security);
        $containerProphecy->get(PeopleRepository::class)->shouldNotBeCalled();

        $queryBuilder = $this->applyExtension($containerProphecy->reveal());

        /** @var Andx $where */
        $where = $queryBuilder->getDQLPart('where');
        self::assertInstanceOf(Andx::class, $where);

        $andParts = $where->getParts();

        self::assertCount(1, $andParts);
        /** @var Orx $orPart */
        $orPart = $andParts[0];
        self::assertInstanceOf(Orx::class, $orPart);

        $orParts = $orPart->getParts();
        self::assertCount(2, $orParts);

        /** @var Comparison $comparison */
        $comparison = $orParts[0];
        self::assertInstanceOf(Comparison::class, $comparison);

        self::assertSame('type_a3.name = :military_type', (string) $comparison);

        /** @var Comparison $comparison */
        $comparison = $orParts[1];
        self::assertInstanceOf(Comparison::class, $comparison);

        self::assertSame('type_a4.name = :military_type', (string) $comparison);

        self::assertSame($queryBuilder->getParameter('military_type')->getValue(), CustomerType::MILITARY_TYPE_NAME);
    }

    public function testFilterAddsRegionConditionWhenUserHasAccess()
    {
        $security = $this->getSecurity(false, false, true, false, false, false, false, $this->getUserWithRegion());

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($security);
        $containerProphecy->get(PeopleRepository::class)->shouldNotBeCalled();
        $containerProphecy->get(FeatureRepository::class)->shouldNotBeCalled();

        $queryBuilder = $this->applyExtension($containerProphecy->reveal());

        /** @var Andx $where */
        $where = $queryBuilder->getDQLPart('where');
        self::assertInstanceOf(Andx::class, $where);

        $orParts = $where->getParts()[0]->getParts();
        self::assertCount(4, $orParts);

        // 3 conditions ASM classiques
        self::assertSame('o.asm = '.self::USER_ID, (string) $orParts[0]);
        self::assertSame('asm_a1.supervisor = '.self::USER_ID, (string) $orParts[1]);
        self::assertSame('supervisor_a2.supervisor = '.self::USER_ID, (string) $orParts[2]);

        // condition région ajoutée
        self::assertInstanceOf(Comparison::class, $orParts[3]);
        self::assertSame('region_a5.id = :user_region_id', (string) $orParts[3]);
        self::assertSame(self::REGION_ID, $queryBuilder->getParameter('user_region_id')->getValue());
    }

    public function testFilterDoesNotAddRegionConditionWhenNoAccess()
    {
        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($this->getUserWithRegion());
        $securityProphecy->isGranted(Argument::any())->willReturn(false);

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());
        $containerProphecy->get(PeopleRepository::class)->shouldNotBeCalled();
        $containerProphecy->get(FeatureRepository::class)->shouldNotBeCalled();

        $queryBuilder = $this->applyExtension($containerProphecy->reveal());

        // doit tomber dans le fallback subscription, sans condition région
        self::assertNotEmpty(array_filter($queryBuilder->getDQLPart('join')['o'], static fn (Join $join) => Subscription::class === $join->getJoin()));
        self::assertSame('subscription_a1.user = :user', (string) $queryBuilder->getDQLPart('where'));
        self::assertNull($queryBuilder->getParameter('user_region_id'));
    }

    public function testFilterRestrictionsAreCumulative()
    {
        $security = $this->getSecurity(false, false, true, false, true, true);

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($security);
        $containerProphecy->get(PeopleRepository::class)->shouldNotBeCalled();
        $featureRepositoryMock = $this->getMockBuilder(FeatureRepository::class)->disableOriginalConstructor()->onlyMethods(['loadFeaturesByPeople'])->getMock();
        $featureRepositoryMock->expects($this->once())->method('loadFeaturesByPeople')->with($this->callback(static fn ($people) => $people instanceof People))->willReturn([
            ['name' => 'FEATURE_SALES_FORECAST_VIEW_SSO', 'location_id' => 1],
            ['name' => 'FEATURE_SALES_FORECAST_VIEW_SSO', 'location_id' => 2],
            ['name' => 'FEATURE_SALES_FORECAST_VIEW_FACTORY', 'location_id' => 3],
            ['name' => 'FEATURE_SALES_FORECAST_VIEW_FACTORY', 'location_id' => 4],
            ['name' => 'FEATURE_SALES_FORECAST_VIEW_FACTORY', 'location_id' => 5],
        ]);
        $containerProphecy->get(FeatureRepository::class)->shouldBeCalledOnce()->willReturn($featureRepositoryMock);

        $queryBuilder = $this->applyExtension($containerProphecy->reveal());

        /** @var Andx $where */
        $where = $queryBuilder->getDQLPart('where');
        self::assertInstanceOf(Andx::class, $where);

        $andParts = $where->getParts();

        /** @var Orx $orPart */
        $orPart = $andParts[0];
        self::assertInstanceOf(Orx::class, $orPart);

        $orParts = $orPart->getParts();
        self::assertCount(5, $orParts);

        /** @var Comparison $comparison1 */
        $comparison1 = $orParts[0];
        self::assertInstanceOf(Comparison::class, $comparison1);
        self::assertSame('o.asm = '.self::USER_ID, (string) $comparison1);

        /** @var Comparison $comparison2 */
        $comparison2 = $orParts[1];
        self::assertInstanceOf(Comparison::class, $comparison2);
        self::assertSame('asm_a1.supervisor = '.self::USER_ID, (string) $comparison2);

        /** @var Comparison $comparison3 */
        $comparison3 = $orParts[2];
        self::assertInstanceOf(Comparison::class, $comparison3);
        self::assertSame('supervisor_a2.supervisor = '.self::USER_ID, (string) $comparison3);

        self::assertSame('o.sso IN(:sso_ids)', (string) $orParts[3]);
        self::assertSame([1, 2], $queryBuilder->getParameter('sso_ids')->getValue());

        self::assertSame('o.factory IN(:factory_ids)', (string) $orParts[4]);
        self::assertSame([3, 4, 5], $queryBuilder->getParameter('factory_ids')->getValue());
    }

    public function getSecurity(bool $full = false, bool $moo = false, bool $asm = false, bool $customer = false, bool $sso = false, bool $factory = false, bool $military = false, ?People $user = null)
    {
        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($user ?? $this->getUser());

        $features = [
            'FEATURE_SALES_FORECAST_VIEW_FULL' => $full,
            'MOO_SFR' => $moo,
            'FEATURE_SALES_FORECAST_VIEW_ASM' => $asm,
            'FEATURE_SALES_FORECAST_VIEW_CUSTOMER' => $customer,
            'FEATURE_SALES_FORECAST_VIEW_SSO' => $sso,
            'FEATURE_SALES_FORECAST_VIEW_FACTORY' => $factory,
            'FEATURE_SALES_FORECAST_VIEW_MILITARY' => $military,
        ];

        foreach ($features as $feature => $granted) {
            $securityProphecy->isGranted($feature)->shouldBeCalledTimes(1)->willReturn($granted);
        }

        return $securityProphecy->reveal();
    }

    private function applyExtension(ContainerInterface $container, $context = []): QueryBuilder
    {
        $emProphecy = $this->prophesize(EntityManagerInterface::class);
        $emProphecy->getExpressionBuilder()->willReturn(new Expr());
        $queryBuilder = new QueryBuilder($emProphecy->reveal());

        $queryBuilder->from(SalesForecast::class, 'o');

        $extension = new SalesForecastExtension($container);

        $extension->applyToCollection($queryBuilder, new QueryNameGenerator(), SalesForecast::class, new GetCollection(), $context);

        return $queryBuilder;
    }

    private function getUserWithRegion(): People
    {
        $region = new Region();
        $refl = new \ReflectionClass($region);
        $refl->getProperty('id')->setValue($region, self::REGION_ID);

        $businessUnit = new BusinessUnit();
        $businessUnit->setRegion($region);

        $people = new People();
        $people->setBusinessUnit($businessUnit);

        $refl = new \ReflectionClass($people);
        $refl->getParentClass()->getProperty('id')->setValue($people, self::USER_ID);

        return $people;
    }

    private function getUser()
    {
        $location = new Location();
        $location->setLegacyId(self::BU_ID);

        $businessUnit = new BusinessUnit();
        $businessUnit->setLocation($location);

        $refl = new \ReflectionClass($location);

        $reflectionProperty = $refl->getProperty('id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($location, self::BU_ID);

        $people = new People();
        $people->setBusinessUnit($businessUnit);

        $refl = new \ReflectionClass($people);

        /** @var \ReflectionClass $user */
        $user = $refl->getParentClass();

        $reflectionProperty = $user->getProperty('id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($people, self::USER_ID);

        return $people;
    }
}
