<?php

declare(strict_types=1);

namespace App\Tests\AI\Tool\Filterable;

use App\AI\Filterable\Definition\Filter;
use App\AI\Filterable\FilterableRegistry;
use App\AI\Filterable\Query\GenericFilterQuery;
use App\AI\Security\EntityAccessCheckerInterface;
use App\AI\Security\EntityAccessCheckerRegistry;
use App\AI\Tool\Filterable\FilterEntityTool;
use App\Tests\AI\Filterable\Fixture\FakeFilterableDefinition;
use App\Tests\AI\Filterable\Fixture\MockEntityManagerFactory;
use PHPUnit\Framework\TestCase;

final class FilterEntityToolTest extends TestCase
{
    use MockEntityManagerFactory;

    public function testReturnsErrorWhenEntityUnknown(): void
    {
        $tool = new FilterEntityTool(new FilterableRegistry([]));

        $result = $tool('missing');

        self::assertArrayHasKey('error', $result);
        self::assertStringContainsString('missing', $result['error']);
        self::assertStringContainsString('list_filterables', $result['error']);
    }

    public function testReturnsValidationErrorWhenFiltersUnknown(): void
    {
        $provider = $this->fakeDefinition([], [Filter::like('allowed', 'someColumn')]);

        $tool = new FilterEntityTool(new FilterableRegistry([$provider]));

        /** @var array<string, mixed> $filters */
        $filters = ['typo' => 'x'];
        $result = $tool('fake', $filters);

        self::assertArrayHasKey('error', $result);
        self::assertStringContainsString('typo', $result['error']);
        // search() never reached → EM mock wasn't asked anything
        self::assertNull($this->capturedLimit);
    }

    public function testWrapsSearchResultsAndComputesCountAndTruncated(): void
    {
        $entities = [new \stdClass(), new \stdClass()];
        $provider = $this->fakeDefinition($entities);

        $tool = new FilterEntityTool(new FilterableRegistry([$provider]));

        $result = $tool('fake');

        self::assertSame('fake', $result['entity']);
        self::assertSame(2, $result['count']);
        self::assertCount(2, $result['results']);
        self::assertFalse($result['truncated']);
        self::assertSame(50, $this->capturedLimit);
    }

    public function testTruncatedFlagWhenResultCountReachesLimit(): void
    {
        $entities = [new \stdClass(), new \stdClass(), new \stdClass()];
        $provider = $this->fakeDefinition($entities);

        $tool = new FilterEntityTool(new FilterableRegistry([$provider]));

        $result = $tool('fake', [], 3);

        self::assertTrue($result['truncated']);
        self::assertSame(3, $this->capturedLimit);
    }

    public function testClampsLimitToMaxBound(): void
    {
        $provider = $this->fakeDefinition([]);
        $tool = new FilterEntityTool(new FilterableRegistry([$provider]));

        $tool('fake', [], 9999);

        self::assertSame(200, $this->capturedLimit);
    }

    public function testReportsErrorWhenSpecRejectsInputAtQueryTime(): void
    {
        // Filter::dateRange now throws \InvalidArgumentException on malformed dates;
        // the tool must catch it and surface the message instead of bubbling up.
        $provider = $this->fakeDefinition([], [Filter::dateRange('claimDate', 'after', 'before')]);
        $tool = new FilterEntityTool(new FilterableRegistry([$provider]));

        /** @var array<string, mixed> $filters */
        $filters = ['after' => 'not-a-date'];
        $result = $tool('fake', $filters);

        self::assertArrayHasKey('error', $result);
        self::assertStringContainsString('ISO-8601 date', $result['error']);
        self::assertArrayNotHasKey('results', $result);
    }

    public function testTruncatedReflectsRawCountNotAccessFilteredCount(): void
    {
        // 3 entities returned from Doctrine, limit 3, but the access checker denies all.
        // results=[] but truncated must still be true (the DB window was saturated).
        $entities = [new \stdClass(), new \stdClass(), new \stdClass()];
        $registry = $this->buildMockManagerRegistry($entities);
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
        $provider = new FakeFilterableDefinition(
            new GenericFilterQuery($registry),
            new EntityAccessCheckerRegistry([$checker]),
            entityName: 'fake',
        );

        $tool = new FilterEntityTool(new FilterableRegistry([$provider]));

        $result = $tool('fake', [], 3);

        self::assertSame(0, $result['count']);
        self::assertSame([], $result['results']);
        self::assertTrue($result['truncated']);
    }

    public function testClampsLimitToMinBound(): void
    {
        $provider = $this->fakeDefinition([]);
        $tool = new FilterEntityTool(new FilterableRegistry([$provider]));

        $tool('fake', [], 0);

        self::assertSame(1, $this->capturedLimit);
    }

    /**
     * @dataProvider normalizeFiltersProvider
     *
     * @param array<mixed>         $input
     * @param array<string, mixed> $expected
     */
    public function testNormalizeFilters(array $input, array $expected): void
    {
        self::assertSame($expected, FilterEntityTool::normalizeFilters($input));
    }

    /**
     * @return iterable<string, array{array<mixed>, array<string, mixed>}>
     */
    public static function normalizeFiltersProvider(): iterable
    {
        yield 'empty stays empty' => [[], []];

        yield 'canonical map is returned unchanged' => [
            ['statuses' => ['PENDING'], 'limit' => 10],
            ['statuses' => ['PENDING'], 'limit' => 10],
        ];

        yield 'list of name/value pairs is remapped' => [
            [
                ['name' => 'statuses', 'value' => ['PENDING']],
                ['name' => 'supplierNumbers', 'value' => ['TIE0002']],
            ],
            ['statuses' => ['PENDING'], 'supplierNumbers' => ['TIE0002']],
        ];

        yield 'list of JSON-encoded strings is decoded then remapped' => [
            [
                json_encode(['name' => 'statuses', 'value' => ['PENDING']]) ?: '',
                json_encode(['name' => 'limit', 'value' => 5]) ?: '',
            ],
            ['statuses' => ['PENDING'], 'limit' => 5],
        ];

        yield 'list items without name key are skipped' => [
            [
                ['name' => 'ok', 'value' => 1],
                ['value' => 'orphan'],
                'plain-string',
            ],
            ['ok' => 1],
        ];

        yield 'list item with missing value becomes null' => [
            [['name' => 'flag']],
            ['flag' => null],
        ];
    }

    /**
     * @param object[]                                   $cannedResult
     * @param \App\AI\Filterable\Definition\FilterSpec[] $fields
     */
    private function fakeDefinition(array $cannedResult, array $fields = []): FakeFilterableDefinition
    {
        $registry = $this->buildMockManagerRegistry($cannedResult);

        return new FakeFilterableDefinition(
            new GenericFilterQuery($registry),
            new EntityAccessCheckerRegistry([]),
            entityName: 'fake',
            fields: $fields,
        );
    }
}
