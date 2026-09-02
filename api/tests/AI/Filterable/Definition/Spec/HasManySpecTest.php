<?php

declare(strict_types=1);

namespace App\Tests\AI\Filterable\Definition\Spec;

use App\AI\Filterable\Definition\Spec\HasManySpec;
use App\Tests\AI\Filterable\Fixture\QueryBuilderFactory;
use PHPUnit\Framework\TestCase;

final class HasManySpecTest extends TestCase
{
    use QueryBuilderFactory;

    public function testToFieldsExposesBoolField(): void
    {
        $fields = (new HasManySpec('hasParts', 'parts'))->toFields();

        self::assertSame('bool', $fields[0]->type);
    }

    public function testApplyToIsNoOpWhenValueAbsent(): void
    {
        [$qb, $paths] = $this->buildQueryBuilderAndResolver();

        (new HasManySpec('hasParts', 'parts'))->applyTo($qb, $paths, []);

        self::assertNull($qb->getDQLPart('where'));
    }

    public function testApplyToAddsIsNotEmptyWhenTrue(): void
    {
        [$qb, $paths] = $this->buildQueryBuilderAndResolver();

        (new HasManySpec('hasParts', 'parts'))->applyTo($qb, $paths, ['hasParts' => true]);

        self::assertStringContainsString('r.parts IS NOT EMPTY', (string) $qb->getDQLPart('where'));
    }

    public function testApplyToAddsIsEmptyWhenFalse(): void
    {
        [$qb, $paths] = $this->buildQueryBuilderAndResolver();

        (new HasManySpec('hasParts', 'parts'))->applyTo($qb, $paths, ['hasParts' => false]);

        self::assertStringContainsString('r.parts IS EMPTY', (string) $qb->getDQLPart('where'));
    }
}
