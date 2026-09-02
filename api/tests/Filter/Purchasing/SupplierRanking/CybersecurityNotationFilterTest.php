<?php

declare(strict_types=1);

namespace App\Tests\Filter\Purchasing\SupplierRanking;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Purchasing\SupplierRanking\SupplierRanking;
use App\Filter\Purchasing\SupplierRanking\CybersecurityNotationFilter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class CybersecurityNotationFilterTest extends TestCase
{
    public function testApplyDoesNothingWhenResourceClassDoesNotMatch(): void
    {
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->expects(self::never())->method('getCurrentRequest');

        $filter = new CybersecurityNotationFilter($requestStack);

        $qb = $this->createQueryBuilder();
        $before = $qb->getDQLParts();

        $filter->apply($qb, $this->createMock(QueryNameGeneratorInterface::class), \stdClass::class);

        self::assertSame($before, $qb->getDQLParts());
    }

    public function testApplyAddsExistsConditionForCybersecurityNotationValue(): void
    {
        $request = new Request(['criteria_9' => '1']);

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn($request);

        $nameGenerator = $this->createMock(QueryNameGeneratorInterface::class);
        $nameGenerator->method('generateJoinAlias')->willReturn('cybersecurity_n1');
        $nameGenerator->method('generateParameterName')->willReturnMap([
            ['cybersecurity_criteria_id', 'cybersecurity_criteria_id_p1'],
            ['cybersecurity_notation_value', 'cybersecurity_notation_value_p1'],
        ]);

        $filter = new CybersecurityNotationFilter($requestStack);

        $qb = $this->createQueryBuilder();

        $filter->apply($qb, $nameGenerator, SupplierRanking::class);

        $dql = $qb->getDQL();

        self::assertStringContainsString('EXISTS (SELECT 1 FROM App\\Entity\\Purchasing\\SupplierRanking\\Notation cybersecurity_n1', $dql);
        self::assertStringContainsString('IDENTITY(cybersecurity_n1.criteria) = :cybersecurity_criteria_id_p1', $dql);
        self::assertStringContainsString('cybersecurity_n1.notation = :cybersecurity_notation_value_p1', $dql);

        self::assertSame(9, $qb->getParameter('cybersecurity_criteria_id_p1')?->getValue());
        self::assertSame(1, $qb->getParameter('cybersecurity_notation_value_p1')?->getValue());
    }

    public function testApplyAddsIsNullConditionForNullNotationValue(): void
    {
        $request = new Request(['criteria_9' => 'null']);

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn($request);

        $nameGenerator = $this->createMock(QueryNameGeneratorInterface::class);
        $nameGenerator->method('generateJoinAlias')->willReturn('cybersecurity_n1');
        $nameGenerator->method('generateParameterName')->willReturnMap([
            ['cybersecurity_criteria_id', 'cybersecurity_criteria_id_p1'],
            ['cybersecurity_notation_value', 'cybersecurity_notation_value_p1'],
        ]);

        $filter = new CybersecurityNotationFilter($requestStack);

        $qb = $this->createQueryBuilder();

        $filter->apply($qb, $nameGenerator, SupplierRanking::class);

        $dql = $qb->getDQL();

        self::assertStringContainsString('IDENTITY(cybersecurity_n1.criteria) = :cybersecurity_criteria_id_p1', $dql);
        self::assertStringContainsString('cybersecurity_n1.notation IS NULL', $dql);
        self::assertNull($qb->getParameter('cybersecurity_notation_value_p1'));
    }

    public function testApplyDoesNothingWhenCybersecurityNotationIsMissing(): void
    {
        $request = new Request();

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn($request);

        $filter = new CybersecurityNotationFilter($requestStack);

        $qb = $this->createQueryBuilder();
        $before = $qb->getDQLParts();

        $filter->apply($qb, $this->createMock(QueryNameGeneratorInterface::class), SupplierRanking::class);

        self::assertSame($before, $qb->getDQLParts());
    }

    public function testGetDescriptionReturnsCybersecurityNotationProperty(): void
    {
        $filter = new CybersecurityNotationFilter($this->createMock(RequestStack::class));

        $description = $filter->getDescription(SupplierRanking::class);

        self::assertArrayHasKey('criteria_9', $description);
        self::assertSame('string', $description['criteria_9']['type']);
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
