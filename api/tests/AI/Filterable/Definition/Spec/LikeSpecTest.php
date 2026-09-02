<?php

declare(strict_types=1);

namespace App\Tests\AI\Filterable\Definition\Spec;

use App\AI\Filterable\Definition\Spec\LikeSpec;
use App\Tests\AI\Filterable\Fixture\QueryBuilderFactory;
use PHPUnit\Framework\TestCase;

final class LikeSpecTest extends TestCase
{
    use QueryBuilderFactory;

    public function testToFieldsExposesStringField(): void
    {
        $spec = new LikeSpec('nameLike', 'name', 'desc');

        $fields = $spec->toFields();

        self::assertCount(1, $fields);
        self::assertSame('nameLike', $fields[0]->name);
        self::assertSame('string', $fields[0]->type);
        self::assertFalse($fields[0]->multi);
    }

    public function testApplyToIsNoOpWhenValueAbsentOrEmpty(): void
    {
        [$qb, $paths] = $this->buildQueryBuilderAndResolver();
        $spec = new LikeSpec('nameLike', 'name');

        $spec->applyTo($qb, $paths, []);
        $spec->applyTo($qb, $paths, ['nameLike' => '']);

        self::assertNull($qb->getDQLPart('where'));
    }

    public function testApplyToWrapsValueWithPercentSigns(): void
    {
        [$qb, $paths] = $this->buildQueryBuilderAndResolver();
        $spec = new LikeSpec('nameLike', 'name');

        $spec->applyTo($qb, $paths, ['nameLike' => 'foo']);

        self::assertStringContainsString('r.name LIKE :p_nameLike', (string) $qb->getDQLPart('where'));
        self::assertSame('%foo%', $qb->getParameter('p_nameLike')?->getValue());
    }
}
