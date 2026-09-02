<?php

declare(strict_types=1);

namespace App\Tests\AI\Tool\Filterable;

use App\AI\Filterable\Definition\Filter;
use App\AI\Filterable\Definition\FilterSpec;
use App\AI\Filterable\FilterableRegistry;
use App\AI\Filterable\Query\GenericFilterQuery;
use App\AI\Security\EntityAccessCheckerRegistry;
use App\AI\Tool\Filterable\DescribeFilterableTool;
use App\Tests\AI\Filterable\Fixture\FakeFilterableDefinition;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;

final class DescribeFilterableToolTest extends TestCase
{
    public function testReturnsErrorWhenEntityUnknown(): void
    {
        $tool = new DescribeFilterableTool(new FilterableRegistry([]));

        $result = $tool('missing');

        self::assertArrayHasKey('error', $result);
        self::assertStringContainsString('missing', $result['error']);
    }

    public function testReturnsSchemaWithNameDescriptionAndFlattenedFields(): void
    {
        $provider = $this->fake('warranty_claim', 'WC entity.', [
            Filter::in('statuses', 'status', enum: ['PENDING', 'CLOSED'], desc: 'Statuses.'),
            Filter::dateRange('claimDate', 'claimDateAfter', 'claimDateBefore'),
        ]);

        $tool = new DescribeFilterableTool(new FilterableRegistry([$provider]));

        $result = $tool('warranty_claim');

        self::assertSame('warranty_claim', $result['name']);
        self::assertSame('WC entity.', $result['description']);

        $fields = $result['fields'];
        self::assertCount(3, $fields, 'in spec → 1 field, dateRange → 2 fields');

        self::assertSame('statuses', $fields[0]['name']);
        self::assertSame('string', $fields[0]['type']);
        self::assertTrue($fields[0]['multi']);
        self::assertSame(['PENDING', 'CLOSED'], $fields[0]['enum']);

        self::assertSame('claimDateAfter', $fields[1]['name']);
        self::assertSame('date', $fields[1]['type']);
        self::assertFalse($fields[1]['multi']);

        self::assertSame('claimDateBefore', $fields[2]['name']);
        self::assertSame('date', $fields[2]['type']);
    }

    /**
     * @param FilterSpec[] $fields
     */
    private function fake(string $name, string $description, array $fields): FakeFilterableDefinition
    {
        return new FakeFilterableDefinition(
            new GenericFilterQuery($this->createMock(ManagerRegistry::class)),
            new EntityAccessCheckerRegistry([]),
            entityName: $name,
            fields: $fields,
            description: $description,
        );
    }
}
