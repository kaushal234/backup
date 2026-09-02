<?php

declare(strict_types=1);

namespace App\Tests\AI\Filterable\Definition\Spec;

use App\AI\Filterable\Definition\Spec\EqSpec;
use App\AI\Filterable\FilterableField;
use App\Tests\AI\Filterable\Fixture\QueryBuilderFactory;
use PHPUnit\Framework\TestCase;

final class EqSpecTest extends TestCase
{
    use QueryBuilderFactory;

    public function testToFieldsExposesScalarField(): void
    {
        $spec = new EqSpec('status', 'status', FilterableField::TYPE_STRING, 'Status');

        $fields = $spec->toFields();

        self::assertCount(1, $fields);
        self::assertSame('status', $fields[0]->name);
        self::assertFalse($fields[0]->multi);
        self::assertSame('Status', $fields[0]->description);
    }

    public function testApplyToIsNoOpWhenValueAbsent(): void
    {
        [$qb, $paths] = $this->buildQueryBuilderAndResolver();
        $spec = new EqSpec('status', 'status', FilterableField::TYPE_STRING);

        $spec->applyTo($qb, $paths, []);

        self::assertNull($qb->getDQLPart('where'));
    }

    public function testApplyToIsNoOpWhenValueIsEmptyString(): void
    {
        [$qb, $paths] = $this->buildQueryBuilderAndResolver();
        $spec = new EqSpec('status', 'status', FilterableField::TYPE_STRING);

        $spec->applyTo($qb, $paths, ['status' => '']);

        self::assertNull($qb->getDQLPart('where'));
    }

    public function testApplyToAddsEqualityClauseWithStringValue(): void
    {
        [$qb, $paths] = $this->buildQueryBuilderAndResolver();
        $spec = new EqSpec('status', 'status', FilterableField::TYPE_STRING);

        $spec->applyTo($qb, $paths, ['status' => 'OPEN']);

        self::assertStringContainsString('r.status = :p_status', (string) $qb->getDQLPart('where'));
        self::assertSame('OPEN', $qb->getParameter('p_status')?->getValue());
    }

    public function testApplyToCastsToIntForIntType(): void
    {
        [$qb, $paths] = $this->buildQueryBuilderAndResolver();
        $spec = new EqSpec('id', 'id', FilterableField::TYPE_INT);

        $spec->applyTo($qb, $paths, ['id' => '42']);

        self::assertSame(42, $qb->getParameter('p_id')?->getValue());
    }
}
