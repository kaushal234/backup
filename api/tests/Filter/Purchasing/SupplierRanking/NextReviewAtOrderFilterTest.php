<?php

declare(strict_types=1);

namespace App\Tests\Filter\Purchasing\SupplierRanking;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Purchasing\SupplierRanking\SupplierRanking;
use App\Filter\Purchasing\SupplierRanking\NextReviewAtOrderFilter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

final class NextReviewAtOrderFilterTest extends TestCase
{
    public function testApplyDoesNothingWhenResourceClassDoesNotMatch(): void
    {
        $filter = new NextReviewAtOrderFilter();

        $qb = $this->createQueryBuilder();
        $before = $qb->getDQLParts();

        $filter->apply($qb, $this->createMock(QueryNameGeneratorInterface::class), \stdClass::class, null, [
            'filters' => ['order' => ['nextReviewAt' => 'asc']],
        ]);

        self::assertSame($before, $qb->getDQLParts());
    }

    public function testApplyDoesNothingWhenDirectionIsInvalid(): void
    {
        $filter = new NextReviewAtOrderFilter();

        $qb = $this->createQueryBuilder();
        $before = $qb->getDQLParts();

        $filter->apply($qb, $this->createMock(QueryNameGeneratorInterface::class), SupplierRanking::class, null, [
            'filters' => ['order' => ['nextReviewAt' => 'invalid']],
        ]);

        self::assertSame($before, $qb->getDQLParts());
    }

    public function testApplyAddsJoinSelectAndOrderBy(): void
    {
        $filter = new NextReviewAtOrderFilter();

        $nameGenerator = $this->createMock(QueryNameGeneratorInterface::class);
        $nameGenerator
            ->method('generateJoinAlias')
            ->willReturnOnConsecutiveCalls('classification_a1', 'periodicity_a2');
        $nameGenerator
            ->method('generateParameterName')
            ->with('nextReviewAtOrder')
            ->willReturn('nextReviewAtOrder_p1');

        $qb = $this->createQueryBuilder();

        $filter->apply($qb, $nameGenerator, SupplierRanking::class, null, [
            'filters' => ['order' => ['nextReviewAt' => 'desc']],
        ]);

        $dql = $qb->getDQL();

        self::assertStringContainsString('LEFT JOIN sr.classification classification_a1', $dql);
        self::assertStringContainsString('LEFT JOIN classification_a1.periodicityByExpertiseLevels periodicity_a2 WITH periodicity_a2.expertiseLevel = sr.expertiseLevel', $dql);
        self::assertStringContainsString("DATE_ADD(sr.lastReviewAt, periodicity_a2.months, 'month') AS HIDDEN nextReviewAtOrder_p1", $dql);
        self::assertStringContainsString('ORDER BY nextReviewAtOrder_p1 DESC', $dql);
    }

    public function testGetDescriptionReturnsFilterForSupplierRankingOnly(): void
    {
        $filter = new NextReviewAtOrderFilter();

        self::assertSame([], $filter->getDescription(\stdClass::class));

        $description = $filter->getDescription(SupplierRanking::class);

        self::assertArrayHasKey('order[nextReviewAt]', $description);
        self::assertSame('nextReviewAt', $description['order[nextReviewAt]']['property']);
    }

    private function createQueryBuilder(): QueryBuilder
    {
        $em = $this->createMock(EntityManagerInterface::class);

        $qb = new QueryBuilder($em);
        $qb->select('sr')->from(SupplierRanking::class, 'sr');

        return $qb;
    }
}
