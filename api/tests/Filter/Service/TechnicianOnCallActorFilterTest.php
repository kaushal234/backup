<?php

declare(strict_types=1);

namespace App\Tests\Filter\Service;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Service\TechnicianOnCall;
use App\Filter\Service\TechnicianOnCallActorFilter;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

class TechnicianOnCallActorFilterTest extends TestCase
{
    private TechnicianOnCallActorFilter $filter;
    private QueryBuilder&\PHPUnit\Framework\MockObject\MockObject $queryBuilder;
    private QueryNameGeneratorInterface&\PHPUnit\Framework\MockObject\MockObject $queryNameGenerator;
    private Operation&\PHPUnit\Framework\MockObject\MockObject $operation;

    protected function setUp(): void
    {
        $this->filter = new TechnicianOnCallActorFilter();
        $this->queryBuilder = $this->createMock(QueryBuilder::class);
        $this->queryNameGenerator = $this->createMock(QueryNameGeneratorInterface::class);
        $this->operation = $this->createMock(Operation::class);
    }

    public function testThrowsExceptionForWrongResourceClass(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('This filter is restricted to the TechnicianOnCall resource');

        $this->filter->apply(
            $this->queryBuilder,
            $this->queryNameGenerator,
            \stdClass::class,
            $this->operation,
            []
        );
    }

    public function testDoesNothingWhenNoActorFilter(): void
    {
        $this->queryBuilder->expects($this->never())->method('andWhere');

        $this->filter->apply(
            $this->queryBuilder,
            $this->queryNameGenerator,
            TechnicianOnCall::class,
            $this->operation,
            []
        );
    }

    public function testDoesNothingWhenActorFilterIsNull(): void
    {
        $this->queryBuilder->expects($this->never())->method('andWhere');

        $this->filter->apply(
            $this->queryBuilder,
            $this->queryNameGenerator,
            TechnicianOnCall::class,
            $this->operation,
            ['filters' => ['actor' => null]]
        );
    }

    public function testAppliesFilterForSingleActor(): void
    {
        $expr = $this->createMock(Expr::class);
        $orX = $this->createMock(Expr\Orx::class);

        $this->queryBuilder
            ->method('getRootAliases')
            ->willReturn(['o']);

        $this->queryBuilder
            ->method('expr')
            ->willReturn($expr);

        $expr->method('orX')->willReturn($orX);

        $this->queryNameGenerator
            ->method('generateParameterName')
            ->willReturnOnConsecutiveCalls('assignee_1', 'technician_1');

        $addedConditions = [];

        $orX->expects($this->exactly(2))
            ->method('add')
            ->willReturnCallback(static function (string $condition) use (&$addedConditions, $orX) {
                $addedConditions[] = $condition;

                return $orX; // ← retourner $orX pour satisfaire le type de retour
            });

        $this->queryBuilder
            ->expects($this->exactly(2))
            ->method('setParameter')
            ->willReturnSelf();

        $this->queryBuilder
            ->expects($this->once())
            ->method('andWhere')
            ->with($orX);

        $this->filter->apply(
            $this->queryBuilder,
            $this->queryNameGenerator,
            TechnicianOnCall::class,
            $this->operation,
            ['filters' => ['actor' => ['/api/users/42']]]
        );

        $this->assertContains('o.assignee = :assignee_1', $addedConditions);
        $this->assertContains('o.technician = :technician_1', $addedConditions);
    }

    public function testAppliesFilterForMultipleActors(): void
    {
        $iris = ['/api/users/1', '/api/users/2'];

        $expr = $this->createMock(Expr::class);
        $orX = $this->createMock(Expr\Orx::class);

        $this->queryBuilder
            ->method('getRootAliases')
            ->willReturn(['o']);

        $this->queryBuilder
            ->method('expr')
            ->willReturn($expr);

        $expr->method('orX')->willReturn($orX);

        $this->queryNameGenerator
            ->method('generateParameterName')
            ->willReturnOnConsecutiveCalls(
                'assignee_1', 'technician_1',
                'assignee_2', 'technician_2'
            );

        $orX->expects($this->exactly(4))->method('add');

        $this->queryBuilder
            ->expects($this->exactly(4))
            ->method('setParameter')
            ->willReturnSelf();

        $this->queryBuilder
            ->expects($this->once())
            ->method('andWhere')
            ->with($orX);

        $this->filter->apply(
            $this->queryBuilder,
            $this->queryNameGenerator,
            TechnicianOnCall::class,
            $this->operation,
            ['filters' => ['actor' => $iris]]
        );
    }
}
