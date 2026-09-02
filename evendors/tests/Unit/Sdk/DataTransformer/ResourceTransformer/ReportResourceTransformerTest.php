<?php

declare(strict_types=1);

namespace App\Tests\Unit\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\DataTransformer\ResourceTransformer\ReportResourceTransformer;
use App\Sdk\DataTransformer\ResourceTransformer\ResourceTransformerInterface;
use App\Sdk\Resource\Report;
use App\Sdk\Resource\ResourceInterface;

/**
 * @extends AbstractResourceTransformerTest<Report>
 *
 * @group unit
 */
final class ReportResourceTransformerTest extends AbstractResourceTransformerTest
{
    public function getCorrectStructures(): iterable
    {
        yield 'with all data, nominal case' => [[
            '@id' => '/reports/1',
            '@type' => 'Report',
            'x' => 'x',
            'y' => 'y',
            'total' => 2,
            'xTotals' => [],
            'yTotals' => [],
            'rows' => [],
            'metadata' => [],
        ]];

        yield 'with some nullable data' => [[
            '@id' => '/reports/1',
            '@type' => 'Report',
            'x' => 'x',
            'y' => null,
            'total' => 2,
            'xTotals' => [],
            'yTotals' => [],
            'rows' => [],
            'metadata' => [],
        ]];
    }

    public function getIncorrectStructures(): iterable
    {
        yield 'no data provided' => [[]];

        yield 'extra field' => [['foo' => 'bar']];

        yield 'not allowed nullable fields' => [[
            '@id' => '/reports/1',
            '@type' => null,
            'x' => null,
            'y' => 'y',
            'total' => null,
            'xTotals' => [],
            'yTotals' => [],
            'rows' => [],
            'metadata' => [],
        ]];
    }

    protected function getResourceClass(): string
    {
        return Report::class;
    }

    protected function getResourceTransformer(): ResourceTransformerInterface
    {
        return new ReportResourceTransformer();
    }

    protected function assertResourceMatchesStructure(ResourceInterface $resource, array $structure, bool $fromCollectionOrPage): void
    {
        self::assertInstanceOf(Report::class, $resource);

        self::assertSame($structure['@id'], $resource->iri);
        self::assertSame($structure['x'], $resource->x);
        self::assertSame($structure['y'], $resource->y);
        self::assertSame($structure['xTotals'], $resource->xTotals);
        self::assertSame($structure['yTotals'], $resource->yTotals);
        self::assertSame($structure['rows'], $resource->rows);
        self::assertSame($structure['metadata'], $resource->metadata);
    }

    protected function supportsCollections(): bool
    {
        return false;
    }

    protected function supportsPage(): bool
    {
        return false;
    }
}
