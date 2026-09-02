<?php

declare(strict_types=1);

namespace App\Sdk\Http\ResourceSourceProvider;

use App\Sdk\Http\DistributedHttpSource;
use App\Sdk\Http\HttpSource;
use App\Sdk\Resource\Report;
use Symfony\Component\HttpFoundation\Request;

use function sprintf;

/**
 * @implements ResourceSourceProviderInterface<Report>
 */
readonly class ReportResourceSourceProvider implements ResourceSourceProviderInterface
{
    public function supports(string $resource): bool
    {
        return Report::class === $resource;
    }

    public function getFindSource(string|array $identifier): ?HttpSource
    {
        return HttpSource::create(Request::METHOD_GET, sprintf('/reports/resource=%s;x=%s;y=%s', $identifier['resource'], $identifier['x'], $identifier['y']),
            [
                'query' => ['options' => $identifier['options']],
            ],
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getUpdateSource(array|string $identifier, array $update): ?HttpSource
    {
        return null;
    }

    public function getDownloadSource(string|array $identifier): ?HttpSource
    {
        return null;
    }

    public function getFindAllSource(array $criteria = []): ?DistributedHttpSource
    {
        return null;
    }

    public function getPaginationSource(int $page, int $itemsPerPage, array $criteria = []): ?HttpSource
    {
        return null;
    }

    public function getExcelSource(array $criteria = []): ?HttpSource
    {
        return null;
    }
}
