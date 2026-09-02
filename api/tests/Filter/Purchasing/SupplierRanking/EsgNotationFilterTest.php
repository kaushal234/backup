<?php

declare(strict_types=1);

namespace App\Tests\Filter\Purchasing\SupplierRanking;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Purchasing\SupplierRanking\SupplierRanking;
use App\Filter\Purchasing\SupplierRanking\EsgNotationFilter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

final class EsgNotationFilterTest extends TestCase
{
    public function testApplyDoesNothingWhenResourceClassDoesNotMatch(): void
    {
        $filter = new EsgNotationFilter();

        $qb = $this->createQueryBuilder();
        $before = $qb->getDQLParts();

        $filter->apply($qb, $this->createMock(QueryNameGeneratorInterface::class), \stdClass::class);

        self::assertSame($before, $qb->getDQLParts());
    }

    public function testApplyAddsExistsConditionForSingleNotationValue(): void
    {
        $nameGenerator = $this->createMock(QueryNameGeneratorInterface::class);
        $nameGenerator->method('generateJoinAlias')->willReturn('esg_n1');
        $nameGenerator->method('generateParameterName')->willReturnMap([
            ['esg_criteria_id', 'esg_criteria_id_p1'],
            ['esg_notation_values', 'esg_notation_values_p1'],
        ]);

        $filter = new EsgNotationFilter();

        $qb = $this->createQueryBuilder();

        $filter->apply(
            $qb,
            $nameGenerator,
            SupplierRanking::class,
            null,
            [
                'filters' => [
                    'criteria_7' => '1',
                ],
            ],
        );

        $dql = $qb->getDQL();

        self::assertStringContainsString('EXISTS', $dql);
        self::assertStringContainsString('IDENTITY(esg_n1.criteria) = :esg_criteria_id_p1', $dql);
        self::assertStringContainsString('esg_n1.notation IN (:esg_notation_values_p1)', $dql);

        self::assertSame(7, $qb->getParameter('esg_criteria_id_p1')?->getValue());
        self::assertSame([1], $qb->getParameter('esg_notation_values_p1')?->getValue());
    }

    public function testApplyAddsIsNullCondition(): void
    {
        $nameGenerator = $this->createMock(QueryNameGeneratorInterface::class);
        $nameGenerator->method('generateJoinAlias')->willReturn('esg_n1');
        $nameGenerator->method('generateParameterName')->willReturnMap([
            ['esg_criteria_id', 'esg_criteria_id_p1'],
            ['esg_notation_values', 'esg_notation_values_p1'],
        ]);

        $filter = new EsgNotationFilter();

        $qb = $this->createQueryBuilder();

        $filter->apply(
            $qb,
            $nameGenerator,
            SupplierRanking::class,
            null,
            [
                'filters' => [
                    'criteria_7' => 'null',
                ],
            ],
        );

        $dql = $qb->getDQL();

        self::assertStringContainsString('esg_n1.notation IS NULL', $dql);
        self::assertNull($qb->getParameter('esg_notation_values_p1'));
    }

    public function testApplySupportsMultipleValues(): void
    {
        $nameGenerator = $this->createMock(QueryNameGeneratorInterface::class);
        $nameGenerator->method('generateJoinAlias')->willReturn('esg_n1');
        $nameGenerator->method('generateParameterName')->willReturnMap([
            ['esg_criteria_id', 'esg_criteria_id_p1'],
            ['esg_notation_values', 'esg_notation_values_p1'],
        ]);

        $filter = new EsgNotationFilter();

        $qb = $this->createQueryBuilder();

        $filter->apply(
            $qb,
            $nameGenerator,
            SupplierRanking::class,
            null,
            [
                'filters' => [
                    'criteria_7' => ['null', '1', '5'],
                ],
            ],
        );

        $dql = $qb->getDQL();

        self::assertStringContainsString('esg_n1.notation IS NULL', $dql);
        self::assertStringContainsString('esg_n1.notation IN (:esg_notation_values_p1)', $dql);

        self::assertSame([1, 5], $qb->getParameter('esg_notation_values_p1')?->getValue());
    }

    public function testApplyDoesNothingWhenFilterIsMissing(): void
    {
        $filter = new EsgNotationFilter();

        $qb = $this->createQueryBuilder();
        $before = $qb->getDQLParts();

        $filter->apply(
            $qb,
            $this->createMock(QueryNameGeneratorInterface::class),
            SupplierRanking::class,
            null,
            [],
        );

        self::assertSame($before, $qb->getDQLParts());
    }

    public function testGetDescriptionReturnsEsgNotationProperty(): void
    {
        $filter = new EsgNotationFilter();

        $description = $filter->getDescription(SupplierRanking::class);

        self::assertArrayHasKey('criteria_7', $description);
        self::assertSame('array', $description['criteria_7']['type']);
    }

    private function createQueryBuilder(): QueryBuilder
    {
        $em = $this->createMock(EntityManagerInterface::class);

        $subQb = new QueryBuilder($em);

        $em->method('createQueryBuilder')->willReturn($subQb);

        $qb = new QueryBuilder($em);
        $qb->select('sr')->from(SupplierRanking::class, 'sr');

        return $qb;
    }
}
