<?php

declare(strict_types=1);

namespace App\Tests\AI\Filterable\Definition\Spec;

use App\AI\Filterable\Definition\Spec\ExistsSpec;
use App\Tests\AI\Filterable\Fixture\QueryBuilderFactory;
use PHPUnit\Framework\TestCase;

final class ExistsSpecTest extends TestCase
{
    use QueryBuilderFactory;

    public function testToFieldsExposesBoolField(): void
    {
        $fields = (new ExistsSpec('hasDate', 'closedAt'))->toFields();

        self::assertSame('bool', $fields[0]->type);
    }

    public function testApplyToIsNoOpWhenValueAbsent(): void
    {
        [$qb, $paths] = $this->buildQueryBuilderAndResolver();

        (new ExistsSpec('hasDate', 'closedAt'))->applyTo($qb, $paths, []);

        self::assertNull($qb->getDQLPart('where'));
    }

    public function testApplyToAddsIsNotNullWhenTrue(): void
    {
        [$qb, $paths] = $this->buildQueryBuilderAndResolver();

        (new ExistsSpec('hasDate', 'closedAt'))->applyTo($qb, $paths, ['hasDate' => true]);

        self::assertStringContainsString('r.closedAt IS NOT NULL', (string) $qb->getDQLPart('where'));
    }

    public function testApplyToAddsIsNullWhenFalse(): void
    {
        [$qb, $paths] = $this->buildQueryBuilderAndResolver();

        (new ExistsSpec('hasDate', 'closedAt'))->applyTo($qb, $paths, ['hasDate' => false]);

        self::assertStringContainsString('r.closedAt IS NULL', (string) $qb->getDQLPart('where'));
    }
}
