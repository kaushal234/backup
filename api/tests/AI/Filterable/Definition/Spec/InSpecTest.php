<?php

declare(strict_types=1);

namespace App\Tests\AI\Filterable\Definition\Spec;

use App\AI\Filterable\Definition\Spec\InSpec;
use App\AI\Filterable\FilterableField;
use App\Tests\AI\Filterable\Fixture\QueryBuilderFactory;
use PHPUnit\Framework\TestCase;

final class InSpecTest extends TestCase
{
    use QueryBuilderFactory;

    public function testToFieldsExposesMultiValuedFieldWithType(): void
    {
        $spec = new InSpec('statuses', 'status', FilterableField::TYPE_STRING, ['A', 'B'], 'Statuses');

        $fields = $spec->toFields();

        self::assertCount(1, $fields);
        self::assertSame('statuses', $fields[0]->name);
        self::assertSame('string', $fields[0]->type);
        self::assertTrue($fields[0]->multi);
        self::assertSame(['A', 'B'], $fields[0]->enum);
        self::assertSame('Statuses', $fields[0]->description);
    }

    public function testApplyToIsNoOpWhenValueAbsent(): void
    {
        [$qb, $paths] = $this->buildQueryBuilderAndResolver();
        $spec = new InSpec('statuses', 'status', FilterableField::TYPE_STRING);

        $spec->applyTo($qb, $paths, []);

        self::assertNull($qb->getDQLPart('where'));
        self::assertCount(0, $qb->getParameters());
    }

    public function testApplyToIsNoOpWhenValueIsEmptyArray(): void
    {
        [$qb, $paths] = $this->buildQueryBuilderAndResolver();
        $spec = new InSpec('statuses', 'status', FilterableField::TYPE_STRING);

        $spec->applyTo($qb, $paths, ['statuses' => []]);

        self::assertNull($qb->getDQLPart('where'));
    }

    public function testApplyToThrowsWhenValueIsNotArray(): void
    {
        [$qb, $paths] = $this->buildQueryBuilderAndResolver();
        $spec = new InSpec('statuses', 'status', FilterableField::TYPE_STRING);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Filter "statuses" expects an array of values, got string. Wrap a single value in an array.');

        $spec->applyTo($qb, $paths, ['statuses' => 'PENDING']);
    }

    public function testApplyToCastsValuesToStringByDefault(): void
    {
        [$qb, $paths] = $this->buildQueryBuilderAndResolver();
        $spec = new InSpec('statuses', 'status', FilterableField::TYPE_STRING);

        $spec->applyTo($qb, $paths, ['statuses' => ['PENDING', 42]]);

        self::assertStringContainsString('r.status IN (:p_statuses)', (string) $qb->getDQLPart('where'));
        self::assertSame(['PENDING', '42'], $qb->getParameter('p_statuses')?->getValue());
    }

    public function testApplyToCastsValuesToIntForIntType(): void
    {
        [$qb, $paths] = $this->buildQueryBuilderAndResolver();
        $spec = new InSpec('ids', 'enteredBy', FilterableField::TYPE_INT);

        $spec->applyTo($qb, $paths, ['ids' => ['1', '2', 3]]);

        self::assertSame([1, 2, 3], $qb->getParameter('p_ids')?->getValue());
    }

    public function testApplyToThrowsWhenValueArrayExceedsMaxValues(): void
    {
        [$qb, $paths] = $this->buildQueryBuilderAndResolver();
        $spec = new InSpec('statuses', 'status', FilterableField::TYPE_STRING);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('Filter "statuses" accepts at most %d values', InSpec::MAX_VALUES));

        $spec->applyTo($qb, $paths, ['statuses' => array_fill(0, InSpec::MAX_VALUES + 1, 'X')]);
    }

    public function testApplyToUsesIdentityShortcutForBareToOneRelation(): void
    {
        [$qb, $paths] = $this->buildQueryBuilderAndResolver(associations: ['enteredBy' => false]);
        $spec = new InSpec('ids', 'enteredBy', FilterableField::TYPE_INT);

        $spec->applyTo($qb, $paths, ['ids' => [1, 2]]);

        $where = (string) $qb->getDQLPart('where');
        self::assertStringContainsString('IDENTITY(r.enteredBy) IN (:p_ids)', $where);
        // No join added.
        self::assertSame([], $qb->getDQLPart('join'));
    }
}
