<?php

declare(strict_types=1);

namespace App\Tests\Filter\Purchasing\SupplierRanking;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Purchasing\SupplierRanking\Criteria;
use App\Entity\Purchasing\SupplierRanking\SupplierRanking;
use App\Filter\Purchasing\SupplierRanking\CriteriaOrderFilter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class CriteriaOrderFilterTest extends TestCase
{
    public function testApplyDoesNothingWhenResourceClassDoesNotMatch(): void
    {
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->expects(self::never())->method('getCurrentRequest');

        $filter = new CriteriaOrderFilter($requestStack, $this->createMock(ManagerRegistry::class));

        $qb = $this->createQueryBuilder();
        $before = $qb->getDQLParts();

        $filter->apply($qb, $this->createMock(QueryNameGeneratorInterface::class), \stdClass::class);

        self::assertSame($before, $qb->getDQLParts());
    }

    public function testApplyDoesNothingWhenOrderIsMissing(): void
    {
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn(new Request());

        $filter = new CriteriaOrderFilter($requestStack, $this->createMock(ManagerRegistry::class));

        $qb = $this->createQueryBuilder();
        $before = $qb->getDQLParts();

        $filter->apply($qb, $this->createMock(QueryNameGeneratorInterface::class), SupplierRanking::class);

        self::assertSame($before, $qb->getDQLParts());
    }

    public function testApplyAddsJoinAndOrderByForCriteriaColumn(): void
    {
        $request = new Request();
        $request->query->set('order', ['criteria_3' => 'asc']);

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn($request);

        $nameGenerator = $this->createMock(QueryNameGeneratorInterface::class);
        $nameGenerator->method('generateJoinAlias')->willReturn('notation_a1');
        $nameGenerator->method('generateParameterName')->willReturn('criteria_3_p1');

        $filter = new CriteriaOrderFilter($requestStack, $this->createMock(ManagerRegistry::class));

        $qb = $this->createQueryBuilder();

        $filter->apply($qb, $nameGenerator, SupplierRanking::class);

        $dql = $qb->getDQL();

        self::assertStringContainsString('LEFT JOIN sr.notations notation_a1 WITH IDENTITY(notation_a1.criteria) = :criteria_3_p1', $dql);
        self::assertStringContainsString('ORDER BY notation_a1.notation ASC, sr.id ASC', $dql);

        $parameter = $qb->getParameter('criteria_3_p1');
        self::assertNotNull($parameter);
        self::assertSame(3, (int) $parameter->getValue());
    }

    public function testGetDescriptionReturnsFallbackWhenNoEntityManager(): void
    {
        $requestStack = $this->createMock(RequestStack::class);

        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $managerRegistry->method('getManagerForClass')->with(Criteria::class)->willReturn(null);

        $filter = new CriteriaOrderFilter($requestStack, $managerRegistry);

        $description = $filter->getDescription(SupplierRanking::class);

        self::assertArrayHasKey('order[criteria_{id}]', $description);
    }

    public function testGetDescriptionReturnsCriteriaEntriesFromDatabaseIds(): void
    {
        $query = $this->createMock(Query::class);
        $query->method('getSingleColumnResult')->willReturn([1, '2']);

        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('select')->with('c.id')->willReturnSelf();
        $qb->method('from')->with(Criteria::class, 'c')->willReturnSelf();
        $qb->method('getQuery')->willReturn($query);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('createQueryBuilder')->willReturn($qb);

        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $managerRegistry->method('getManagerForClass')->with(Criteria::class)->willReturn($entityManager);

        $filter = new CriteriaOrderFilter($this->createMock(RequestStack::class), $managerRegistry);

        $description = $filter->getDescription(SupplierRanking::class);

        self::assertArrayHasKey('order[criteria_1]', $description);
        self::assertArrayHasKey('order[criteria_2]', $description);
        self::assertSame('criteria_1', $description['order[criteria_1]']['property']);
    }

    private function createQueryBuilder(): QueryBuilder
    {
        $em = $this->createMock(EntityManagerInterface::class);

        $qb = new QueryBuilder($em);
        $qb->select('sr')->from(SupplierRanking::class, 'sr');

        return $qb;
    }
}
