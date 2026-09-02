<?php

declare(strict_types=1);

namespace App\Tests\AI\Filterable;

use App\AI\Filterable\Definition\Filter;
use App\AI\Filterable\Definition\FilterSpec;
use App\AI\Filterable\FilterableRegistry;
use App\AI\Filterable\Query\GenericFilterQuery;
use App\AI\Security\EntityAccessCheckerRegistry;
use App\Tests\AI\Filterable\Fixture\FakeFilterableDefinition;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;

final class FilterableRegistryTest extends TestCase
{
    public function testIndexesProvidersByName(): void
    {
        $a = $this->fake('foo');
        $b = $this->fake('bar');

        $registry = new FilterableRegistry([$a, $b]);

        self::assertSame(['foo' => $a, 'bar' => $b], $registry->all());
        self::assertSame($a, $registry->get('foo'));
        self::assertSame($b, $registry->get('bar'));
    }

    public function testGetReturnsNullForUnknownName(): void
    {
        $registry = new FilterableRegistry([$this->fake('foo')]);

        self::assertNull($registry->get('missing'));
    }

    public function testValidateFiltersReturnsNullWhenAllKeysAllowed(): void
    {
        $provider = $this->fake('e', [
            Filter::in('statuses', 'status'),
            Filter::like('customerNameLike', 'customerName'),
        ]);

        $registry = new FilterableRegistry([$provider]);

        /** @var array<string, mixed> $filters */
        $filters = ['statuses' => ['PENDING'], 'customerNameLike' => 'foo'];
        self::assertNull($registry->validateFilters($provider, $filters));
    }

    public function testValidateFiltersReturnsErrorMessageListingUnknownAndAllowed(): void
    {
        $provider = $this->fake('warranty_claim', [
            Filter::in('statuses', 'status'),
        ]);

        $registry = new FilterableRegistry([$provider]);

        /** @var array<string, mixed> $filters */
        $filters = ['statuses' => ['PENDING'], 'typo' => 'x', 'other' => 1];
        $error = $registry->validateFilters($provider, $filters);

        self::assertNotNull($error);
        self::assertStringContainsString('warranty_claim', $error);
        self::assertStringContainsString('typo', $error);
        self::assertStringContainsString('other', $error);
        self::assertStringContainsString('statuses', $error);
    }

    public function testValidateFiltersHandlesRangeSpecExpandingToTwoFields(): void
    {
        $provider = $this->fake('e', [
            Filter::dateRange('claimDate', 'claimDateAfter', 'claimDateBefore'),
        ]);

        $registry = new FilterableRegistry([$provider]);

        /** @var array<string, mixed> $okFilters */
        $okFilters = ['claimDateAfter' => '2020-01-01', 'claimDateBefore' => '2020-12-31'];
        self::assertNull($registry->validateFilters($provider, $okFilters));

        /** @var array<string, mixed> $badFilters */
        $badFilters = ['claimDate' => '2020-01-01'];
        $error = $registry->validateFilters($provider, $badFilters);
        self::assertNotNull($error);
        self::assertStringContainsString('claimDate', $error);
    }

    public function testEmptyRegistry(): void
    {
        $registry = new FilterableRegistry([]);

        self::assertSame([], $registry->all());
        self::assertNull($registry->get('anything'));
    }

    /**
     * @param FilterSpec[] $fields
     */
    private function fake(string $name, array $fields = []): FakeFilterableDefinition
    {
        return new FakeFilterableDefinition(
            new GenericFilterQuery($this->createMock(ManagerRegistry::class)),
            new EntityAccessCheckerRegistry([]),
            entityName: $name,
            fields: $fields,
        );
    }
}
