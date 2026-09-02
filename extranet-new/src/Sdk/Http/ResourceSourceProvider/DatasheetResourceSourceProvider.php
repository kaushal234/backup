<?php

declare(strict_types=1);

namespace App\Sdk\Http\ResourceSourceProvider;

use App\Sdk\Http\HttpSource;
use App\Sdk\Resource\Datasheet;
use Symfony\Component\HttpFoundation\Request;

class DatasheetResourceSourceProvider extends AbstractResourceSourceProvider
{
    public function supports(string $resource): bool
    {
        return Datasheet::class === $resource;
    }

    /**
     * @param string|array<string, string> $identifier
     */
    public function getFindSource(array|string $identifier): ?HttpSource
    {
        return null;
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
        return null;
    }

    public function getResourceIri(): string
    {
        return 'sales/product_family_dms';
    }
}
