<?php

declare(strict_types=1);

namespace App\Tests\Filter;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\EquipmentRecord;
use App\Filter\ExcludeFilter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ExcludeFilterTest extends TestCase
{
    use ProphecyTrait;

    public function testQueryBuilderIsModifiedWhenFilterIsApplied()
    {
        $emProphecy = $this->prophesize(EntityManagerInterface::class);
        $emProphecy->getExpressionBuilder()->willReturn(new Expr());
        $queryBuilder = new QueryBuilder($emProphecy->reveal());
        $queryBuilder->from(EquipmentRecord::class, 'e');

        $queryNameGeneratorProphecy = $this->prophesize(QueryNameGeneratorInterface::class);

        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $requestStackProphecy = $this->prophesize(RequestStack::class);

        $requestStackProphecy->getCurrentRequest()->shouldBeCalledOnce()->willReturn(new Request(['exclude' => '/equipment_records/1']));
        $iriConverterProphecy->getResourceFromIri(Argument::cetera())->shouldBeCalledOnce()->willReturn(new EquipmentRecord());

        $filter = new ExcludeFilter($iriConverterProphecy->reveal(), $requestStackProphecy->reveal());
        $filter->apply($queryBuilder, $queryNameGeneratorProphecy->reveal(), EquipmentRecord::class);

        /** @var Andx $where */
        $where = $queryBuilder->getDQLPart('where');
        $this->assertInstanceOf(Andx::class, $where);

        /** @var Andx $andPart */
        $andPart = $where->getParts()[0];

        $this->assertSame('e.id <> :id', (string) $andPart);
    }
}
