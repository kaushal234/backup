<?php

declare(strict_types=1);

namespace App\Sdk\Http\ResourceSourceProvider;

use App\Sdk\Http\HttpSource;
use App\Sdk\Resource\NonConformityMainFile;
use Symfony\Component\HttpFoundation\Request;

use function sprintf;

/**
 * @implements ResourceSourceProviderInterface<NonConformityMainFile>
 */
class NonConformityMainFileResourceSourceProvider implements ResourceSourceProviderInterface
{
    public function supports(string $resource): bool
    {
        return NonConformityMainFile::class === $resource;
    }

    public function getFindSource(array|string $identifier): ?HttpSource
    {
        return null;
    }

    public function getUpdateSource(array|string $identifier, array $update): ?HttpSource
    {
        return null;
    }

    public function getFindAllSource(array $criteria = []): ?HttpSource
    {
        return null;
    }

    public function getPaginationSource(int $page, int $itemsPerPage, array $criteria = []): ?HttpSource
    {
        return null;
    }

    public function getDownloadSource(string|array $identifier): HttpSource
    {
        return HttpSource::create(Request::METHOD_GET, sprintf('quality/non_conformities/%s/main_file/%s', $identifier['resource_id'], $identifier['file_id']));
    }

    public function getExcelSource(array $criteria = []): ?HttpSource
    {
        return null;
    }
}
