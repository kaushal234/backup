<?php

declare(strict_types=1);

namespace App\Sdk\Http\ResourceSourceProvider;

use App\Sdk\Http\HttpSource;
use App\Sdk\Resource\EquipmentRecord;
use Symfony\Component\HttpFoundation\Request;

class EquipmentRecordResourceSourceProvider extends AbstractResourceSourceProvider
{
    public function supports(string $resource): bool
    {
        return EquipmentRecord::class === $resource;
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
        $uri = \sprintf('/equipment_records/%d/extranet_update', $identifier['resource_id']);

        return HttpSource::create(Request::METHOD_PUT, $uri, [
            'json' => $payload,
        ]);
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
                'normalization_groups' => ['iata_code_detail'],
                ...$criteria,
            ],
        ]);
    }

    public function getResourceIri(): string
    {
        return 'equipment_records';
    }
}
