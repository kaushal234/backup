<?php

declare(strict_types=1);

namespace App\Tests\Filter\MIS\Module;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use App\Entity\Module\Module;
use App\Entity\Module\ThirdPartyApp\Type\Extended;
use App\Filter\MIS\Module\ModuleWithOpenUpdateTasksOnlyFilter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class ModuleWithOpenUpdateTasksOnlyFilterTest extends TestCase
{
    use ProphecyTrait;

    public function testFilterIsAppliedWhenEnabled(): void
    {
        $requestStackProphecy = $this->prophesize(RequestStack::class);
        $requestStackProphecy
            ->getCurrentRequest()
            ->shouldBeCalledTimes(1)
            ->willReturn(new Request([ModuleWithOpenUpdateTasksOnlyFilter::FILTER_PROPERTY => '1']));

        $filter = new ModuleWithOpenUpdateTasksOnlyFilter($requestStackProphecy->reveal());

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->from(Module::class, 'o');

        $filter->apply($queryBuilder, new QueryNameGenerator(), Module::class);

        /** @var Andx|null $where */
        $where = $queryBuilder->getDQLPart('where');
        self::assertInstanceOf(Andx::class, $where);

        $andParts = $where->getParts();
        self::assertCount(2, $andParts);

        $first = (string) $andParts[0];
        $second = (string) $andParts[1];

        $all = $first.' '.$second;

        self::assertStringContainsString('INSTANCE OF', $all);
        self::assertStringContainsString(Extended::class, $all);

        self::assertStringContainsString('EXISTS', $all);
        self::assertStringContainsString('done = false', $all);
        self::assertStringContainsString('thirdPartyApp = o', $all);
    }

    public function testFilterIsNotAppliedWhenDisabled(): void
    {
        $requestStackProphecy = $this->prophesize(RequestStack::class);
        $requestStackProphecy
            ->getCurrentRequest()
            ->shouldBeCalledTimes(1)
            ->willReturn(new Request([ModuleWithOpenUpdateTasksOnlyFilter::FILTER_PROPERTY => '0']));

        $filter = new ModuleWithOpenUpdateTasksOnlyFilter($requestStackProphecy->reveal());

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->from(Module::class, 'o');

        $filter->apply($queryBuilder, new QueryNameGenerator(), Module::class);
    }

    public function testFilterIsNotAppliedWhenMissing(): void
    {
        $requestStackProphecy = $this->prophesize(RequestStack::class);
        $requestStackProphecy
            ->getCurrentRequest()
            ->shouldBeCalledTimes(1)
            ->willReturn(new Request([]));

        $filter = new ModuleWithOpenUpdateTasksOnlyFilter($requestStackProphecy->reveal());

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->from(Module::class, 'o');

        $filter->apply($queryBuilder, new QueryNameGenerator(), Module::class);
    }

    private function getQueryBuilder(): QueryBuilder
    {
        $emProphecy = $this->prophesize(EntityManagerInterface::class);
        $emProphecy->getExpressionBuilder()->willReturn(new Expr());

        $subEmProphecy = $this->prophesize(EntityManagerInterface::class);
        $subEmProphecy->getExpressionBuilder()->willReturn(new Expr());

        $subQueryBuilder = new QueryBuilder($subEmProphecy->reveal());

        $emProphecy->createQueryBuilder()->willReturn($subQueryBuilder);

        return new QueryBuilder($emProphecy->reveal());
    }
}
