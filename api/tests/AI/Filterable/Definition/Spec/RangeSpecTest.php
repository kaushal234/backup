<?php

declare(strict_types=1);

namespace App\Tests\AI\Filterable\Definition\Spec;

use App\AI\Filterable\Definition\Spec\RangeSpec;
use App\AI\Filterable\FilterableField;
use App\Tests\AI\Filterable\Fixture\QueryBuilderFactory;
use PHPUnit\Framework\TestCase;

final class RangeSpecTest extends TestCase
{
    use QueryBuilderFactory;

    public function testToFieldsExposesTwoFieldsWithDefaultDateDescriptions(): void
    {
        $spec = new RangeSpec('claimDate', 'claimDateAfter', 'claimDateBefore', FilterableField::TYPE_DATE);

        $fields = $spec->toFields();

        self::assertCount(2, $fields);
        self::assertSame('claimDateAfter', $fields[0]->name);
        self::assertSame('date', $fields[0]->type);
        self::assertStringContainsString('on/after', $fields[0]->description);
        self::assertSame('claimDateBefore', $fields[1]->name);
        self::assertStringContainsString('on/before', $fields[1]->description);
    }

    public function testToFieldsAllowsPerBoundDescriptionOverride(): void
    {
        $spec = new RangeSpec('h', 'minH', 'maxH', FilterableField::TYPE_INT, '', 'min desc', 'max desc');

        $fields = $spec->toFields();

        self::assertSame('min desc', $fields[0]->description);
        self::assertSame('max desc', $fields[1]->description);
    }

    public function testToFieldsPrefixesGenericDescriptionsWithSharedDesc(): void
    {
        $spec = new RangeSpec('h', 'minH', 'maxH', FilterableField::TYPE_INT, 'Equipment hours.');

        $fields = $spec->toFields();

        self::assertStringStartsWith('Equipment hours. ', $fields[0]->description);
        self::assertStringStartsWith('Equipment hours. ', $fields[1]->description);
    }

    public function testApplyToIsNoOpWhenBothBoundsAbsent(): void
    {
        [$qb, $paths] = $this->buildQueryBuilderAndResolver();
        $spec = new RangeSpec('claimDate', 'after', 'before', FilterableField::TYPE_DATE);

        $spec->applyTo($qb, $paths, []);
        $spec->applyTo($qb, $paths, ['after' => '', 'before' => null]);

        self::assertNull($qb->getDQLPart('where'));
    }

    public function testApplyToAddsOnlyLowerBoundWhenUpperAbsent(): void
    {
        [$qb, $paths] = $this->buildQueryBuilderAndResolver();
        $spec = new RangeSpec('claimDate', 'after', 'before', FilterableField::TYPE_DATE);

        $spec->applyTo($qb, $paths, ['after' => '2020-01-01']);

        $where = (string) $qb->getDQLPart('where');
        self::assertStringContainsString('r.claimDate >= :p_after', $where);
        self::assertStringNotContainsString('<=', $where);
        self::assertInstanceOf(\DateTimeImmutable::class, $qb->getParameter('p_after')?->getValue());
    }

    public function testApplyToAddsBothBoundsWhenProvided(): void
    {
        [$qb, $paths] = $this->buildQueryBuilderAndResolver();
        $spec = new RangeSpec('claimDate', 'after', 'before', FilterableField::TYPE_DATE);

        $spec->applyTo($qb, $paths, ['after' => '2020-01-01', 'before' => '2020-12-31']);

        $where = (string) $qb->getDQLPart('where');
        self::assertStringContainsString('>= :p_after', $where);
        self::assertStringContainsString('<= :p_before', $where);
    }

    public function testApplyToCoercesIntValues(): void
    {
        [$qb, $paths] = $this->buildQueryBuilderAndResolver();
        $spec = new RangeSpec('hours', 'minH', 'maxH', FilterableField::TYPE_INT);

        $spec->applyTo($qb, $paths, ['minH' => '100', 'maxH' => '500']);

        self::assertSame(100, $qb->getParameter('p_minH')?->getValue());
        self::assertSame(500, $qb->getParameter('p_maxH')?->getValue());
    }

    public function testApplyToCoercesFloatValues(): void
    {
        [$qb, $paths] = $this->buildQueryBuilderAndResolver();
        $spec = new RangeSpec('amount', 'minA', 'maxA', FilterableField::TYPE_FLOAT);

        $spec->applyTo($qb, $paths, ['minA' => '1.5']);

        self::assertSame(1.5, $qb->getParameter('p_minA')?->getValue());
    }

    public function testApplyToThrowsWhenDateStringInvalid(): void
    {
        [$qb, $paths] = $this->buildQueryBuilderAndResolver();
        $spec = new RangeSpec('d', 'after', 'before', FilterableField::TYPE_DATE);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Filter "after" expects an ISO-8601 date, got "not-a-date".');

        $spec->applyTo($qb, $paths, ['after' => 'not-a-date']);
    }

    public function testApplyToThrowsOnUnsupportedType(): void
    {
        [$qb, $paths] = $this->buildQueryBuilderAndResolver();
        $spec = new RangeSpec('s', 'minS', 'maxS', FilterableField::TYPE_STRING);

        $this->expectException(\LogicException::class);
        $spec->applyTo($qb, $paths, ['minS' => 'abc']);
    }
}
