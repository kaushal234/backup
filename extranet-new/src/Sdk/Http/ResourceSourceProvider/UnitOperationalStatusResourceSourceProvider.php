<?php

declare(strict_types=1);

namespace App\Sdk\Http\ResourceSourceProvider;

use App\Sdk\Http\HttpSource;
use App\Sdk\Resource\UnitOperationalStatus;
use Symfony\Component\HttpFoundation\Request;

class UnitOperationalStatusResourceSourceProvider extends AbstractResourceSourceProvider
{
    public function supports(string $resource): bool
    {
        return UnitOperationalStatus::class === $resource;
    }

    /**
     * @param string|array<string, string> $identifier
     */
    public function getFindSource(array|string $identifier): ?HttpSource
    {
        return HttpSource::create(Request::METHOD_GET, \sprintf('%s/%s', $this->getResourceIri(), $identifier['resource_id']));
    }

    /**
     * @param string|array<string, string> $identifier
     */
    public function getDownloadSource(array|string $identifier): ?HttpSource
    {
        return null;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function getCreateSource(array $payload): ?HttpSource
    {
        return null;
    }

    /**
     * @param string|array<string, string> $identifier
     * @param array<string, mixed>         $payload
     */
    public function getUpdateSource(array|string $identifier, array $payload): ?HttpSource
    {
        return null;
    }

    /**
     * @param array<string, mixed> $criteria
     */
    public function getFindAllSource(array $criteria = []): ?HttpSource
    {
        return HttpSource::create(Request::METHOD_GET, $this->getResourceIri(), $criteria);
    }

    public function getPaginationSource(int $page, int $itemsPerPage, array $criteria = []): ?HttpSource
    {
        return HttpSource::create(Request::METHOD_GET, $this->getResourceIri(), [
            'query' => [
                'pagination' => true,
                'page' => $page,
                'itemsPerPage' => $itemsPerPage,
                ...$criteria,
            ],
        ]);
    }

    public function getResourceIri(): string
    {
        return 'unit_operational_statuses';
    }
}
