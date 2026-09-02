<?php

declare(strict_types=1);

namespace App\Tests\AI\Tool\Filterable;

use App\AI\Filterable\FilterableRegistry;
use App\AI\Filterable\Query\GenericFilterQuery;
use App\AI\Security\EntityAccessCheckerRegistry;
use App\AI\Tool\Filterable\ListFilterablesTool;
use App\Tests\AI\Filterable\Fixture\FakeFilterableDefinition;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;

final class ListFilterablesToolTest extends TestCase
{
    public function testReturnsEmptyEntitiesArrayWhenRegistryEmpty(): void
    {
        $tool = new ListFilterablesTool(new FilterableRegistry([]));

        self::assertSame(['entities' => []], $tool());
    }

    public function testReturnsNameAndDescriptionForEachRegisteredProvider(): void
    {
        $a = $this->fake('warranty_claim', 'Warranty claims (WC).');
        $b = $this->fake('vendor_warranty_claim', 'Vendor warranty claims (VWC).');

        $tool = new ListFilterablesTool(new FilterableRegistry([$a, $b]));

        self::assertSame([
            'entities' => [
                ['name' => 'warranty_claim', 'description' => 'Warranty claims (WC).'],
                ['name' => 'vendor_warranty_claim', 'description' => 'Vendor warranty claims (VWC).'],
            ],
        ], $tool());
    }

    private function fake(string $name, string $description): FakeFilterableDefinition
    {
        return new FakeFilterableDefinition(
            new GenericFilterQuery($this->createMock(ManagerRegistry::class)),
            new EntityAccessCheckerRegistry([]),
            entityName: $name,
            description: $description,
        );
    }
}
