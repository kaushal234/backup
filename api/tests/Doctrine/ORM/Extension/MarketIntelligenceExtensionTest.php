<?php

declare(strict_types=1);

namespace App\Tests\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use App\Doctrine\ORM\Extension\MarketIntelligenceExtension;
use App\Entity\Directory\People;
use App\Entity\Directory\Position;
use App\Entity\Directory\PositionLevel;
use App\Entity\Sales\MarketIntelligence\MarketIntelligence;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class MarketIntelligenceExtensionTest extends TestCase
{
    use ProphecyTrait;

    public function testLinkedMIMAreFilteredForTheItem()
    {
        $securityProphecy = $this->prophesize(Security::class);

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());

        $securityProphecy->getUser()->shouldBeCalledOnce()->willReturn((new People())->setPosition((new Position())->setLevel(new PositionLevel())));
        $emProphecy = $this->prophesize(EntityManagerInterface::class);
        $emProphecy->getExpressionBuilder()->willReturn(new Expr());
        $queryBuilder = new QueryBuilder($emProphecy->reveal());

        $queryBuilder->from(MarketIntelligence::class, 'o');

        $queryBuilder->join('o.marketIntelligencesLinked', 'l');

        $extension = new MarketIntelligenceExtension($containerProphecy->reveal());

        $extension->applyToItem($queryBuilder, new QueryNameGenerator(), MarketIntelligence::class, []);

        /** @var Andx $where */
        $where = $queryBuilder->getDQLPart('where');
        self::assertInstanceOf(Andx::class, $where);

        $parts = $where->getParts();

        self::assertCount(1, $parts);
        self::assertInstanceOf(Orx::class, $parts[0]);

        /** @var Orx $conditions */
        $conditions = $parts[0];

        $firstEqual = $conditions->getParts()[0];
        $secondEqual = $conditions->getParts()[2];
        self::assertInstanceOf(Expr\Comparison::class, $firstEqual);
        self::assertSame('positionLevelLinked', $firstEqual->getLeftExpr());
        self::assertSame(':userPositionLevels', $firstEqual->getRightExpr());
        self::assertSame('=', $firstEqual->getOperator());

        self::assertInstanceOf(Expr\Comparison::class, $secondEqual);
        self::assertSame('l.poster', $secondEqual->getLeftExpr());
        self::assertSame(':user', $secondEqual->getRightExpr());
        self::assertSame('=', $secondEqual->getOperator());

        self::assertSame('positionLevelLinked IS NULL', $conditions->getParts()[1]);

        self::assertCount(2, $queryBuilder->getParameters());
        self::assertNotNull($queryBuilder->getParameter('userPositionLevels'));
        self::assertNotNull($queryBuilder->getParameter('user'));
    }

    public function testCollectionIsFilteredByDefault()
    {
        $securityProphecy = $this->prophesize(Security::class);

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());

        $securityProphecy->getUser()->shouldBeCalledOnce()->willReturn((new People())->setPosition((new Position())->setLevel(new PositionLevel())));
        $emProphecy = $this->prophesize(EntityManagerInterface::class);
        $emProphecy->getExpressionBuilder()->willReturn(new Expr());
        $queryBuilder = new QueryBuilder($emProphecy->reveal());

        $queryBuilder->from(MarketIntelligence::class, 'o');
        $queryBuilder->join('o.marketIntelligencesLinked', 'l');

        $extension = new MarketIntelligenceExtension($containerProphecy->reveal());

        $extension->applyToCollection($queryBuilder, new QueryNameGenerator(), MarketIntelligence::class);

        /** @var Andx $where */
        $where = $queryBuilder->getDQLPart('where');
        self::assertInstanceOf(Andx::class, $where);

        $parts = $where->getParts();

        self::assertCount(2, $parts);
        self::assertInstanceOf(Orx::class, $parts[0]);
        self::assertInstanceOf(Orx::class, $parts[1]);

        /** @var Orx $conditions */
        $conditions = $parts[0];

        $firstEqual = $conditions->getParts()[0];
        $secondEqual = $conditions->getParts()[2];
        self::assertInstanceOf(Expr\Comparison::class, $firstEqual);
        self::assertSame('positionLevel', $firstEqual->getLeftExpr());
        self::assertSame(':userPositionLevels', $firstEqual->getRightExpr());
        self::assertSame('=', $firstEqual->getOperator());

        self::assertInstanceOf(Expr\Comparison::class, $secondEqual);
        self::assertSame('o.poster', $secondEqual->getLeftExpr());
        self::assertSame(':user', $secondEqual->getRightExpr());
        self::assertSame('=', $secondEqual->getOperator());

        self::assertSame('positionLevel IS NULL', $conditions->getParts()[1]);

        /** @var Orx $conditions */
        $joinConditions = $parts[1];
        $joinFirstEqual = $joinConditions->getParts()[0];
        $joinSsecondEqual = $joinConditions->getParts()[2];
        self::assertInstanceOf(Expr\Comparison::class, $joinFirstEqual);
        self::assertSame('positionLevelLinked', $joinFirstEqual->getLeftExpr());
        self::assertSame(':userPositionLevels', $joinFirstEqual->getRightExpr());
        self::assertSame('=', $joinFirstEqual->getOperator());

        self::assertInstanceOf(Expr\Comparison::class, $joinSsecondEqual);
        self::assertSame('l.poster', $joinSsecondEqual->getLeftExpr());
        self::assertSame(':user', $joinSsecondEqual->getRightExpr());
        self::assertSame('=', $joinSsecondEqual->getOperator());

        self::assertCount(2, $queryBuilder->getParameters());
        self::assertNotNull($queryBuilder->getParameter('userPositionLevels'));
        self::assertNotNull($queryBuilder->getParameter('user'));
    }
}
