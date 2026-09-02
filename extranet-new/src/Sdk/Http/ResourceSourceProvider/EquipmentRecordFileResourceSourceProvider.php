<?php

declare(strict_types=1);

namespace App\Sdk\Http\ResourceSourceProvider;

use App\CQRS\Command\User\FileCommandInterface;
use App\Sdk\Http\HttpSource;
use App\Sdk\Resource\EquipmentRecordFile;
use Symfony\Component\HttpFoundation\Request;

class EquipmentRecordFileResourceSourceProvider extends AbstractResourceSourceProvider
{
    public function supports(string $resource): bool
    {
        return EquipmentRecordFile::class === $resource;
    }

    /**
     * @param array<string, mixed> $criteria
     */
    public function getFindAllSource(array $criteria = []): ?HttpSource
    {
        return HttpSource::create(Request::METHOD_GET, $this->getResourceIri(), $criteria);
    }

    /**
     * @param string|array<string, string> $identifier
     */
    public function getDownloadSource(array|string $identifier): ?HttpSource
    {
        $uri = \sprintf(
            '%s/%s/download',
            $this->getResourceIri(),
            $identifier['id'],
        );

        return HttpSource::create(Request::METHOD_GET, $uri);
    }

    /**
     * @param string|array<string, string> $identifier
     */
    public function getFindSource(array|string $identifier): ?HttpSource
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

    public function getUploadSource(FileCommandInterface $fileCommand): ?HttpSource
    {
        return null;
    }

    public function getPaginationSource(int $page, int $itemsPerPage, array $criteria = []): ?HttpSource
    {
        return null;
    }

    public function getResourceIri(): string
    {
        return 'legacy/equipment_record_files';
    }
}
