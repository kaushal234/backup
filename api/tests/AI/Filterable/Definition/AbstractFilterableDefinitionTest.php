<?php

declare(strict_types=1);

namespace App\Tests\AI\Filterable\Definition;

use App\AI\Filterable\Definition\Filter;
use App\AI\Filterable\Query\GenericFilterQuery;
use App\AI\Security\EntityAccessCheckerInterface;
use App\AI\Security\EntityAccessCheckerRegistry;
use App\Tests\AI\Filterable\Fixture\FakeFilterableDefinition;
use App\Tests\AI\Filterable\Fixture\MockEntityManagerFactory;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;

final class AbstractFilterableDefinitionTest extends TestCase
{
    use MockEntityManagerFactory;

    public function testFieldSchemaFlattensFieldsIncludingRangeSpecs(): void
    {
        $def = $this->fake([
            Filter::in('statuses', 'status'),
            Filter::dateRange('claimDate', 'claimDateAfter', 'claimDateBefore'),
            Filter::like('nameLike', 'name'),
        ]);

        $names = array_map(static fn ($f) => $f->name, $def->fieldSchema());

        self::assertSame(['statuses', 'claimDateAfter', 'claimDateBefore', 'nameLike'], $names);
    }

    public function testFieldSchemaEmptyWhenNoFields(): void
    {
        $def = $this->fake([]);

        self::assertSame([], $def->fieldSchema());
    }

    public function testDefaultAliasIsLowercasedFirstLetterOfEntityShortName(): void
    {
        $def = new FakeFilterableDefinition(
            new GenericFilterQuery($this->createMock(ManagerRegistry::class)),
            new EntityAccessCheckerRegistry([]),
            entityClass: \stdClass::class,
        );

        self::assertSame('s', $def->defaultAlias());
    }

    public function testDefaultOrderIsEmptyByDefault(): void
    {
        $def = new FakeFilterableDefinition(
            new GenericFilterQuery($this->createMock(ManagerRegistry::class)),
            new EntityAccessCheckerRegistry([]),
        );

        self::assertSame([], $def->defaultOrder());
    }

    public function testSearchReturnsAllSummariesWhenNoAccessCheckerRegistered(): void
    {
        [$a, $b, $c] = [new \stdClass(), new \stdClass(), new \stdClass()];
        $registry = $this->buildMockManagerRegistry([$a, $b, $c]);

        $def = new FakeFilterableDefinition(
            new GenericFilterQuery($registry),
            new EntityAccessCheckerRegistry([]),
            summarizer: static fn (object $e): array => ['id' => spl_object_id($e)],
        );

        $outcome = $def->search([], 10);

        self::assertSame(3, $outcome->rawCount);
        self::assertSame([
            ['id' => spl_object_id($a)],
            ['id' => spl_object_id($b)],
            ['id' => spl_object_id($c)],
        ], $outcome->results);
    }

    public function testSearchFiltersOutEntitiesDeniedByAccessChecker(): void
    {
        [$granted, $denied, $alsoGranted] = [new \stdClass(), new \stdClass(), new \stdClass()];
        $registry = $this->buildMockManagerRegistry([$granted, $denied, $alsoGranted]);

        $checker = new class($denied) implements EntityAccessCheckerInterface {
            public function __construct(private readonly object $denied)
            {
            }

            public function supports(string $class): bool
            {
                return \stdClass::class === $class;
            }

            public function isGranted(object $entity): bool
            {
                return $entity !== $this->denied;
            }
        };

        $def = new FakeFilterableDefinition(
            new GenericFilterQuery($registry),
            new EntityAccessCheckerRegistry([$checker]),
            summarizer: static fn (object $e): array => ['id' => spl_object_id($e)],
        );

        $outcome = $def->search([], 10);

        self::assertSame(3, $outcome->rawCount);
        // Results are re-indexed via array_values after filtering.
        self::assertSame([0, 1], array_keys($outcome->results));
        self::assertSame(spl_object_id($granted), $outcome->results[0]['id']);
        self::assertSame(spl_object_id($alsoGranted), $outcome->results[1]['id']);
    }

    public function testSearchReturnsEmptyArrayWhenAllResultsDenied(): void
    {
        $registry = $this->buildMockManagerRegistry([new \stdClass(), new \stdClass()]);

        $checker = new class implements EntityAccessCheckerInterface {
            public function supports(string $class): bool
            {
                return true;
            }

            public function isGranted(object $entity): bool
            {
                return false;
            }
        };

        $def = new FakeFilterableDefinition(
            new GenericFilterQuery($registry),
            new EntityAccessCheckerRegistry([$checker]),
        );

        $outcome = $def->search([], 10);

        self::assertSame(2, $outcome->rawCount);
        self::assertSame([], $outcome->results);
    }

    /**
     * @param \App\AI\Filterable\Definition\FilterSpec[] $fields
     */
    private function fake(array $fields): FakeFilterableDefinition
    {
        return new FakeFilterableDefinition(
            new GenericFilterQuery($this->createMock(ManagerRegistry::class)),
            new EntityAccessCheckerRegistry([]),
            fields: $fields,
        );
    }
}
