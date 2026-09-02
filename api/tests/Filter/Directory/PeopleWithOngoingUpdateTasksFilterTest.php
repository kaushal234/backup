<?php

declare(strict_types=1);

namespace App\Tests\Filter\Directory;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use App\Entity\Directory\People;
use App\Filter\Directory\PeopleWithOngoingUpdateTasksFilter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;

final class PeopleWithOngoingUpdateTasksFilterTest extends TestCase
{
    public function testNoOpWhenResourceIsNotPeople(): void
    {
        $filter = new PeopleWithOngoingUpdateTasksFilter($this->createMock(ManagerRegistry::class));

        $qb = $this->createQueryBuilder();
        $beforeDql = $qb->getDQLParts();

        $filter->apply(
            $qb,
            new QueryNameGenerator(),
            \stdClass::class,
            null,
            ['filters' => ['withOngoingUpdateTasks' => 'true']]
        );

        self::assertSame($beforeDql, $qb->getDQLParts());
    }

    public function testNoOpWhenParamIsAbsent(): void
    {
        $filter = new PeopleWithOngoingUpdateTasksFilter($this->createMock(ManagerRegistry::class));

        $qb = $this->createQueryBuilder();
        $beforeDql = $qb->getDQLParts();

        $filter->apply(
            $qb,
            new QueryNameGenerator(),
            People::class,
            null,
            ['filters' => []]
        );

        self::assertSame($beforeDql, $qb->getDQLParts());
    }

    public function testNoOpWhenParamIsUnparseable(): void
    {
        $filter = new PeopleWithOngoingUpdateTasksFilter($this->createMock(ManagerRegistry::class));

        $qb = $this->createQueryBuilder();
        $beforeDql = $qb->getDQLParts();

        $filter->apply(
            $qb,
            new QueryNameGenerator(),
            People::class,
            null,
            ['filters' => ['withOngoingUpdateTasks' => 'maybe']]
        );

        self::assertSame($beforeDql, $qb->getDQLParts());
    }

    /**
     * @dataProvider provideTruthyValues
     */
    public function testAddsExistsClauseWhenParamIsTrue(string $rawValue): void
    {
        $filter = new PeopleWithOngoingUpdateTasksFilter($this->createMock(ManagerRegistry::class));

        $qb = $this->createQueryBuilder();

        $filter->apply(
            $qb,
            new QueryNameGenerator(),
            People::class,
            null,
            ['filters' => ['withOngoingUpdateTasks' => $rawValue]]
        );

        $where = (string) $qb->getDQLPart('where');
        self::assertStringContainsString('EXISTS', $where);
        self::assertStringNotContainsString('NOT EXISTS', $where);
        self::assertStringContainsString('updateTask.done = false', $where);
        self::assertStringContainsString('updateTask.user = p.id', $where);
    }

    /**
     * @dataProvider provideFalsyValues
     */
    public function testAddsNotExistsClauseWhenParamIsFalse(string $rawValue): void
    {
        $filter = new PeopleWithOngoingUpdateTasksFilter($this->createMock(ManagerRegistry::class));

        $qb = $this->createQueryBuilder();

        $filter->apply(
            $qb,
            new QueryNameGenerator(),
            People::class,
            null,
            ['filters' => ['withOngoingUpdateTasks' => $rawValue]]
        );

        $where = (string) $qb->getDQLPart('where');
        self::assertStringContainsString('NOT(EXISTS', $where);
        self::assertStringContainsString('updateTask.done = false', $where);
    }

    public static function provideTruthyValues(): iterable
    {
        yield "'true'" => ['true'];
        yield "'1'" => ['1'];
    }

    public static function provideFalsyValues(): iterable
    {
        yield "'false'" => ['false'];
        yield "'0'" => ['0'];
    }

    private function createQueryBuilder(): QueryBuilder
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getExpressionBuilder')->willReturn(new Expr());
        $em->method('createQueryBuilder')->willReturnCallback(static fn () => new QueryBuilder($em));

        $qb = new QueryBuilder($em);
        $qb->select('p')->from(People::class, 'p');

        return $qb;
    }
}
