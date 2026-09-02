<?php

declare(strict_types=1);

namespace App\Tests\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Metadata\GetCollection;
use App\Doctrine\ORM\Extension\SalesForecastMyAreaExtension;
use App\Entity\Directory\People;
use App\Entity\Sales\SalesForecast;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class SalesForecastMyAreaExtensionTest extends TestCase
{
    use ProphecyTrait;

    public function testExtensionOnlyAppliesToSalesForecasts()
    {
        $emProphecy = $this->prophesize(EntityManagerInterface::class);
        $emProphecy->getExpressionBuilder()->willReturn(new Expr());
        $queryBuilder = new QueryBuilder($emProphecy->reveal());

        $queryBuilder->from(SalesForecast::class, 'o');

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->get(Security::class)->shouldNotBeCalled();

        $extension = new SalesForecastMyAreaExtension($containerProphecy->reveal());

        $extension->applyToCollection($queryBuilder, new QueryNameGenerator(), \stdClass::class, new GetCollection(name: 'my_area'));

        self::assertNull($queryBuilder->getDQLPart('where'));
    }

    public function testExtensionOnlyAppliesToRouteMyArea()
    {
        $emProphecy = $this->prophesize(EntityManagerInterface::class);
        $emProphecy->getExpressionBuilder()->willReturn(new Expr());
        $queryBuilder = new QueryBuilder($emProphecy->reveal());

        $queryBuilder->from(SalesForecast::class, 'o');

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->get(Security::class)->shouldNotBeCalled();

        $extension = new SalesForecastMyAreaExtension($containerProphecy->reveal());

        $extension->applyToCollection($queryBuilder, new QueryNameGenerator(), SalesForecast::class, new GetCollection(name: 'toute_la_sainte_journée'));

        self::assertNull($queryBuilder->getDQLPart('where'));
    }

    public function testExtensionRestrictsToMyArea()
    {
        $people = new People();
        $refl = new \ReflectionClass($people);

        /** @var \ReflectionClass $user */
        $user = $refl->getParentClass();

        $reflectionProperty = $user->getProperty('id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($people, 12);

        $emProphecy = $this->prophesize(EntityManagerInterface::class);
        $emProphecy->getExpressionBuilder()->willReturn(new Expr());
        $queryBuilder = new QueryBuilder($emProphecy->reveal());

        $queryBuilder->from(SalesForecast::class, 'o');

        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($people);

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());

        $extension = new SalesForecastMyAreaExtension($containerProphecy->reveal());

        $extension->applyToCollection($queryBuilder, new QueryNameGenerator(), SalesForecast::class, new GetCollection(name: 'my_area'));

        /** @var Andx $where */
        $where = $queryBuilder->getDQLPart('where');
        self::assertInstanceOf(Andx::class, $where);

        $andParts = $where->getParts();
        self::assertCount(1, $andParts);

        self::assertInstanceOf(Andx::class, $andParts[0]);
        self::assertSame('salesAreas_a3.asm = 12 AND o.asm != :current_user', (string) $andParts[0]);

        self::assertSame($people, $queryBuilder->getParameter('current_user')->getValue());
    }
}
