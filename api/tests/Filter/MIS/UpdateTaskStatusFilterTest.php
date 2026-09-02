<?php

declare(strict_types=1);

namespace App\Tests\Filter\MIS;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use App\Entity\Module\Module;
use App\Entity\Module\ThirdPartyApp\UpdateTask;
use App\Filter\MIS\UpdateTaskStatusFilter;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bridge\Doctrine\ManagerRegistry;

class UpdateTaskStatusFilterTest extends TestCase
{
    use ProphecyTrait;

    public function testExceptionOnWrongResource(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('This filter is restricted to UpdateTask resource');

        $managerRegistry = $this->prophesize(ManagerRegistry::class);
        $queryBuilder = $this->prophesize(QueryBuilder::class);

        $filter = new UpdateTaskStatusFilter($managerRegistry->reveal());

        $filter->apply(
            queryBuilder: $queryBuilder->reveal(),
            queryNameGenerator: new QueryNameGenerator(),
            resourceClass: Module::class,
            context: ['filters' => ['status' => null]]
        );
    }

    public function testNotAppliedOnWrongProperty(): void
    {
        $managerRegistry = $this->prophesize(ManagerRegistry::class);
        $queryBuilder = $this->prophesize(QueryBuilder::class);

        $filter = new UpdateTaskStatusFilter($managerRegistry->reveal());

        $queryBuilder->andWhere()->shouldNotBeCalled();

        $filter->apply(
            queryBuilder: $queryBuilder->reveal(),
            queryNameGenerator: new QueryNameGenerator(),
            resourceClass: UpdateTask::class,
            context: ['filters' => ['id' => null]]
        );
    }

    public function testApplyInProgress(): void
    {
        $managerRegistry = $this->prophesize(ManagerRegistry::class);
        $queryBuilder = $this->prophesize(QueryBuilder::class);

        $filter = new UpdateTaskStatusFilter($managerRegistry->reveal());

        $queryBuilder->andWhere('o.done = 0')->shouldBeCalledOnce()->willReturn($queryBuilder->reveal());

        $filter->apply(
            queryBuilder: $queryBuilder->reveal(),
            queryNameGenerator: new QueryNameGenerator(),
            resourceClass: UpdateTask::class,
            context: ['filters' => ['status' => 'IN_PROGRESS']]
        );
    }

    public function testApplyConfirmed(): void
    {
        $managerRegistry = $this->prophesize(ManagerRegistry::class);
        $queryBuilder = $this->prophesize(QueryBuilder::class);

        $filter = new UpdateTaskStatusFilter($managerRegistry->reveal());

        $queryBuilder
            ->andWhere('o.done = 1 AND o.confirmed = 1')
            ->shouldBeCalledOnce()
            ->willReturn($queryBuilder->reveal());

        $filter->apply(
            queryBuilder: $queryBuilder->reveal(),
            queryNameGenerator: new QueryNameGenerator(),
            resourceClass: UpdateTask::class,
            context: ['filters' => ['status' => 'CONFIRMED']]
        );
    }

    public function testApplyDenied(): void
    {
        $managerRegistry = $this->prophesize(ManagerRegistry::class);
        $queryBuilder = $this->prophesize(QueryBuilder::class);

        $filter = new UpdateTaskStatusFilter($managerRegistry->reveal());

        $queryBuilder
            ->andWhere('o.done = 1 AND o.confirmed = 0')
            ->shouldBeCalledOnce()
            ->willReturn($queryBuilder->reveal());

        $filter->apply(
            queryBuilder: $queryBuilder->reveal(),
            queryNameGenerator: new QueryNameGenerator(),
            resourceClass: UpdateTask::class,
            context: ['filters' => ['status' => 'DENIED']]
        );
    }

    public function testInvalidStatus(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Not a valid update task status');

        $managerRegistry = $this->prophesize(ManagerRegistry::class);
        $queryBuilder = $this->prophesize(QueryBuilder::class);

        $filter = new UpdateTaskStatusFilter($managerRegistry->reveal());

        $queryBuilder->andWhere()->shouldNotBeCalled();

        $filter->apply(
            queryBuilder: $queryBuilder->reveal(),
            queryNameGenerator: new QueryNameGenerator(),
            resourceClass: UpdateTask::class,
            context: ['filters' => ['status' => 'INVALID_STATUS']]
        );
    }
}
