<?php

declare(strict_types=1);

namespace App\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\Exception\FailedTransformationException;
use App\Sdk\Resource\Report;
use Psl\Collection\AccessibleCollectionInterface;
use Psl\Type;

/**
 * @implements ResourceTransformerInterface<Report>
 */
final class ReportResourceTransformer implements ResourceTransformerInterface
{
    public function supports(string $resource, mixed $data): bool
    {
        if (Report::class !== $resource) {
            return false;
        }

        return Report::getTypeStructure()->matches($data);
    }

    public function transform(mixed $data): Report
    {
        try {
            $structure = Report::getTypeStructure()->assert($data);
        } catch (Type\Exception\AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a report structure.', previous: $e);
        }

        return new Report(
            iri: $structure['@id'],
            x: $structure['x'],
            total: $structure['total'],
            y: $structure['y'],
            xTotals: $structure['xTotals'],
            yTotals: $structure['yTotals'],
            rows: $structure['rows'],
            metadata: $structure['metadata'],
        );
    }

    public function supportsCollection(string $resource, mixed $data): bool
    {
        return false;
    }

    public function transformCollection(mixed $data): AccessibleCollectionInterface
    {
        throw new FailedTransformationException('Collection is not supported for Report resource.');
    }

    public function supportsPage(string $resource, mixed $data): bool
    {
        return false;
    }

    public function transformPage(mixed $data, int $page, int $itemsPerPage): never
    {
        throw new FailedTransformationException('Pagination is not supported for Report resource.');
    }
}
