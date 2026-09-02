<?php

declare(strict_types=1);

namespace App\Sdk\Http\ResourceSourceProvider;

use App\Sdk\Http\HttpSource;
use App\Sdk\Resource\EquipmentSerial;
use Symfony\Component\HttpFoundation\Request;

class EquipmentSerialResourceSourceProvider extends AbstractResourceSourceProvider
{
    public function supports(string $resource): bool
    {
        return EquipmentSerial::class === $resource;
    }

    /**
     * @param string|array<string, string> $identifier
     */
    public function getFindSource(array|string $identifier): ?HttpSource
    {
        return null;
    }

    public function getDownloadSource(array|string $identifier): ?HttpSource
    {
        return HttpSource::create(Request::METHOD_GET, \sprintf('ion/bill-of-materials/extranet_drawings/%s/%s/site=%d;project=;product=%s',
            $identifier['project'],
            $identifier['signalCode'],
            $identifier['site'],
            $identifier['product'],
        ));
    }

    public function getCreateSource(array $payload): ?HttpSource
    {
        return null;
    }

    public function getUpdateSource(array|string $identifier, array $payload): ?HttpSource
    {
        return null;
    }

    /**
     * @param array<string, mixed> $criteria
     */
    public function getFindAllSource(array $criteria = []): ?HttpSource
    {
        return HttpSource::create(Request::METHOD_GET, $this->getResourceIri(), [
            'query' => $criteria,
        ]);
    }

    public function getPaginationSource(int $page, int $itemsPerPage, array $criteria = []): ?HttpSource
    {
        return null;
    }

    public function getResourceIri(): string
    {
        return 'equipment_serials';
    }
}
