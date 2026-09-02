<?php

declare(strict_types=1);

namespace App\Tests\Unit\Filter\MIS\Module;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Module\Module;
use App\Filter\MIS\Module\OrderByOpenUpdateTasksCountFilter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class OrderByOpenUpdateTasksCountFilterTest extends TestCase
{
    public function testItDoesNothingWhenQueryParamIsMissing(): void
    {
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn(new Request());

        $filter = new OrderByOpenUpdateTasksCountFilter($requestStack);

        $qb = $this->createQueryBuilder();
        $before = $qb->getDQLParts();

        $filter->apply(
            $qb,
            $this->createMock(QueryNameGeneratorInterface::class),
            Module::class
        );

        self::assertSame($before, $qb->getDQLParts(), 'QueryBuilder should not be modified when order[countUpdateTasks] is absent.');
    }

    public function testItDoesNothingWhenResourceIsNotModule(): void
    {
        $request = new Request();
        $request->query->set('order', ['countUpdateTasks' => 'desc']);

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn($request);

        $filter = new OrderByOpenUpdateTasksCountFilter($requestStack);

        $qb = $this->createQueryBuilder();
        $before = $qb->getDQLParts();

        $filter->apply(
            $qb,
            $this->createMock(QueryNameGeneratorInterface::class),
            \stdClass::class
        );

        self::assertSame($before, $qb->getDQLParts(), 'QueryBuilder should not be modified for other resources.');
    }

    public function testItAddsJoinGroupByAndOrderByDescWhenRequested(): void
    {
        $request = new Request();
        $request->query->set('order', ['countUpdateTasks' => 'desc']);

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn($request);

        $nameGenerator = $this->createMock(QueryNameGeneratorInterface::class);
        $nameGenerator->method('generateJoinAlias')->willReturn('ut');

        $filter = new OrderByOpenUpdateTasksCountFilter($requestStack);

        $qb = $this->createQueryBuilder();

        $filter->apply($qb, $nameGenerator, Module::class);

        $dql = $qb->getDQL();

        self::assertStringContainsString('COUNT(DISTINCT ut.id) AS HIDDEN openUpdateTasksCount', $dql);

        self::assertStringContainsString('LEFT JOIN App\Entity\Module\ThirdPartyApp\UpdateTask ut WITH', $dql);
        self::assertStringContainsString('ut.thirdPartyApp = m', $dql);
        self::assertStringContainsString('ut.done = false', $dql);

        self::assertStringContainsString('GROUP BY m.id', $dql);

        self::assertStringContainsString('ORDER BY openUpdateTasksCount DESC', $dql);
    }

    public function testItOrdersAscWhenRequested(): void
    {
        $request = new Request();
        $request->query->set('order', ['countUpdateTasks' => 'asc']);

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn($request);

        $nameGenerator = $this->createMock(QueryNameGeneratorInterface::class);
        $nameGenerator->method('generateJoinAlias')->willReturn('ut');

        $filter = new OrderByOpenUpdateTasksCountFilter($requestStack);

        $qb = $this->createQueryBuilder();

        $filter->apply($qb, $nameGenerator, Module::class);

        $dql = $qb->getDQL();
        self::assertStringContainsString('ORDER BY openUpdateTasksCount ASC', $dql);
    }

    private function createQueryBuilder(): QueryBuilder
    {
        $em = $this->createMock(EntityManagerInterface::class);

        $qb = new QueryBuilder($em);
        $qb->select('m')->from(Module::class, 'm');

        return $qb;
    }
}
