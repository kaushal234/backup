<?php

declare(strict_types=1);

namespace App\Sdk\Http\ResourceSourceProvider;

use App\Sdk\Http\HttpSource;
use App\Sdk\Resource\EvendorsNewsFile;
use Symfony\Component\HttpFoundation\Request;

use function sprintf;

/**
 * @implements ResourceSourceProviderInterface<EvendorsNewsFile>
 */
class EvendorsNewsFileResourceSourceProvider implements ResourceSourceProviderInterface
{
    public function supports(string $resource): bool
    {
        return EvendorsNewsFile::class === $resource;
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
        return HttpSource::create(Request::METHOD_GET, sprintf('evendors_news/%s/files/%s', $identifier['resource_id'], $identifier['file_id']));
    }

    public function getExcelSource(array $criteria = []): ?HttpSource
    {
        return null;
    }
}
