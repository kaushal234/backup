<?php

declare(strict_types=1);

namespace App\Sdk\Http\ResourceSourceProvider;

use App\Sdk\Http\HttpSource;
use App\Sdk\Resource\Location;
use App\Sdk\Resource\PurchaseOrder;
use App\Security\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @implements ResourceSourceProviderInterface<PurchaseOrder>
 */
class LocationResourceSourceProvider implements ResourceSourceProviderInterface
{
    public function __construct(
        protected readonly Security $security,
    ) {
    }

    public function supports(string $resource): bool
    {
        return Location::class === $resource;
    }

    public function getFindSource(string|array $identifier): ?HttpSource
    {
        return null;
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

    public function getFindAllSource(array $criteria = []): ?HttpSource
    {
        return HttpSource::create(Request::METHOD_GET, '/locations', ['query' => $criteria]);
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
