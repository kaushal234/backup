<?php

declare(strict_types=1);

namespace App\Tests\AI\Filterable\Definition\Spec;

use App\AI\Filterable\Definition\Spec\InAnySpec;
use App\Tests\AI\Filterable\Fixture\QueryBuilderFactory;
use PHPUnit\Framework\TestCase;

final class InAnySpecTest extends TestCase
{
    use QueryBuilderFactory;

    public function testToFieldsExposesMultiValuedField(): void
    {
        $spec = new InAnySpec('codes', ['c1', 'c2']);

        $fields = $spec->toFields();

        self::assertCount(1, $fields);
        self::assertSame('codes', $fields[0]->name);
        self::assertTrue($fields[0]->multi);
    }

    public function testApplyToIsNoOpWhenValueNotArrayOrEmpty(): void
    {
        [$qb, $paths] = $this->buildQueryBuilderAndResolver();
        $spec = new InAnySpec('codes', ['failureCode1', 'failureCode2']);

        $spec->applyTo($qb, $paths, []);
        $spec->applyTo($qb, $paths, ['codes' => []]);
        $spec->applyTo($qb, $paths, ['codes' => 'scalar']);

        self::assertNull($qb->getDQLPart('where'));
    }

    public function testApplyToBuildsOrAcrossColumnsSharingASingleParameter(): void
    {
        [$qb, $paths] = $this->buildQueryBuilderAndResolver();
        $spec = new InAnySpec('codes', ['failureCode1', 'failureCode2']);

        $spec->applyTo($qb, $paths, ['codes' => ['A', 1]]);

        $where = (string) $qb->getDQLPart('where');
        self::assertStringContainsString('r.failureCode1 IN (:p_codes)', $where);
        self::assertStringContainsString('r.failureCode2 IN (:p_codes)', $where);
        self::assertStringContainsString('OR', $where);
        self::assertSame(['A', '1'], $qb->getParameter('p_codes')?->getValue());
    }
}
