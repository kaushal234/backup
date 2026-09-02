<?php

declare(strict_types=1);

namespace App\Tests\AI\Filterable\Definition\Spec;

use App\AI\Filterable\Definition\Spec\ConcatLikeSpec;
use App\Tests\AI\Filterable\Fixture\QueryBuilderFactory;
use PHPUnit\Framework\TestCase;

final class ConcatLikeSpecTest extends TestCase
{
    use QueryBuilderFactory;

    public function testToFieldsExposesStringField(): void
    {
        $spec = new ConcatLikeSpec('fullName', ['firstname', 'lastname'], 'Full name');

        $fields = $spec->toFields();

        self::assertCount(1, $fields);
        self::assertSame('fullName', $fields[0]->name);
        self::assertFalse($fields[0]->multi);
    }

    public function testApplyToIsNoOpWhenValueAbsentOrEmpty(): void
    {
        [$qb, $paths] = $this->buildQueryBuilderAndResolver();
        $spec = new ConcatLikeSpec('fullName', ['firstname', 'lastname']);

        $spec->applyTo($qb, $paths, []);
        $spec->applyTo($qb, $paths, ['fullName' => '']);

        self::assertNull($qb->getDQLPart('where'));
    }

    public function testApplyToConcatsPathsWithSpacesAndWrapsWithPercent(): void
    {
        [$qb, $paths] = $this->buildQueryBuilderAndResolver();
        $spec = new ConcatLikeSpec('fullName', ['firstname', 'lastname']);

        $spec->applyTo($qb, $paths, ['fullName' => 'john']);

        $where = (string) $qb->getDQLPart('where');
        self::assertStringContainsString("CONCAT(r.firstname, ' ', r.lastname) LIKE :p_fullName", $where);
        self::assertSame('%john%', $qb->getParameter('p_fullName')?->getValue());
    }
}
