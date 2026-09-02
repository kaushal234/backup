<?php

declare(strict_types=1);

namespace App\Sdk\Http\ResourceSourceProvider;

use App\Sdk\Http\HttpSource;
use App\Sdk\Internal\Utility;
use App\Sdk\Resource\NCRVendorWarrantyClaim;
use Symfony\Component\HttpFoundation\Request;

/**
 * @implements ResourceSourceProviderInterface<NCRVendorWarrantyClaim>
 */
final class NCRVendorWarrantyClaimResourceSourceProvider implements ResourceSourceProviderInterface
{
    public function supports(string $resource): bool
    {
        return NCRVendorWarrantyClaim::class === $resource;
    }

    public function getFindSource(array|string $identifier): ?HttpSource
    {
        return HttpSource::create(Request::METHOD_GET, Utility::buildIri('/purchasing/ncr_vendor_warranty_claims/%s', $identifier));
    }

    public function getUpdateSource(array|string $identifier, array $update): ?HttpSource
    {
        return null;
    }

    public function getDownloadSource(array|string $identifier): ?HttpSource
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

    public function getExcelSource(array $criteria = []): ?HttpSource
    {
        return null;
    }
}
