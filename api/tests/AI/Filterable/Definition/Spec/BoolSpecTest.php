<?php

declare(strict_types=1);

namespace App\Tests\AI\Filterable\Definition\Spec;

use App\AI\Filterable\Definition\Spec\BoolSpec;
use App\Tests\AI\Filterable\Fixture\QueryBuilderFactory;
use PHPUnit\Framework\TestCase;

final class BoolSpecTest extends TestCase
{
    use QueryBuilderFactory;

    public function testToFieldsExposesBoolField(): void
    {
        $spec = new BoolSpec('isPrivate', 'isPrivate', true, false);

        $fields = $spec->toFields();

        self::assertCount(1, $fields);
        self::assertSame('bool', $fields[0]->type);
        self::assertFalse($fields[0]->multi);
    }

    public function testApplyToIsNoOpWhenValueAbsent(): void
    {
        [$qb, $paths] = $this->buildQueryBuilderAndResolver();
        $spec = new BoolSpec('flag', 'flag', true, false);

        $spec->applyTo($qb, $paths, []);

        self::assertNull($qb->getDQLPart('where'));
    }

    public function testApplyToBindsTrueValueWhenTruthy(): void
    {
        [$qb, $paths] = $this->buildQueryBuilderAndResolver();
        $spec = new BoolSpec('isPrivate', 'isPrivate', 'Y', 'N');

        $spec->applyTo($qb, $paths, ['isPrivate' => true]);

        self::assertStringContainsString('r.isPrivate = :p_isPrivate', (string) $qb->getDQLPart('where'));
        self::assertSame('Y', $qb->getParameter('p_isPrivate')?->getValue());
    }

    public function testApplyToBindsFalseValueWhenFalsy(): void
    {
        [$qb, $paths] = $this->buildQueryBuilderAndResolver();
        $spec = new BoolSpec('isPrivate', 'isPrivate', 'Y', 'N');

        $spec->applyTo($qb, $paths, ['isPrivate' => false]);

        self::assertSame('N', $qb->getParameter('p_isPrivate')?->getValue());
    }
}
