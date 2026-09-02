<?php

declare(strict_types=1);

namespace App\Tests\AI\Filterable\Definition\Spec;

use App\AI\Filterable\Definition\PathResolver;
use App\AI\Filterable\Definition\Spec\CustomSpec;
use App\AI\Filterable\FilterableField;
use App\Tests\AI\Filterable\Fixture\QueryBuilderFactory;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

final class CustomSpecTest extends TestCase
{
    use QueryBuilderFactory;

    public function testToFieldsReturnsDeclaredFieldsVerbatim(): void
    {
        $fields = [
            new FilterableField('a', FilterableField::TYPE_STRING),
            new FilterableField('b', FilterableField::TYPE_INT),
        ];

        $spec = new CustomSpec($fields, static fn (): null => null);

        self::assertSame($fields, $spec->toFields());
    }

    public function testApplyToInvokesClosureWithQueryBuilderResolverAndFilters(): void
    {
        [$qb, $paths] = $this->buildQueryBuilderAndResolver();

        $captured = null;
        $spec = new CustomSpec(
            [],
            static function (QueryBuilder $q, PathResolver $p, array $f) use (&$captured): void {
                $captured = [$q, $p, $f];
                $q->andWhere('1 = 1');
            },
        );

        $spec->applyTo($qb, $paths, ['k' => 'v']);

        self::assertNotNull($captured);
        self::assertSame($qb, $captured[0]);
        self::assertSame($paths, $captured[1]);
        self::assertSame(['k' => 'v'], $captured[2]);
        self::assertStringContainsString('1 = 1', (string) $qb->getDQLPart('where'));
    }
}
