<?php

declare(strict_types=1);

namespace App\Sdk\Http\ResourceSourceProvider;

use App\Sdk\Http\HttpSource;
use App\Sdk\Internal\Utility;
use App\Sdk\Resource\WCVendorWarrantyClaim;
use Symfony\Component\HttpFoundation\Request;

use function sprintf;

/**
 * @implements ResourceSourceProviderInterface<WCVendorWarrantyClaim>
 */
final class WCVendorWarrantyClaimResourceSourceProvider implements ResourceSourceProviderInterface
{
    public function supports(string $resource): bool
    {
        return WCVendorWarrantyClaim::class === $resource;
    }

    public function getFindSource(array|string $identifier): ?HttpSource
    {
        return HttpSource::create(Request::METHOD_GET, Utility::buildIri('/purchasing/wc_vendor_warranty_claims/%s', $identifier));
    }

    public function getUpdateSource(array|string $identifier, array $update): ?HttpSource
    {
        return null;
    }

    public function getDownloadSource(array|string $identifier): ?HttpSource
    {
        return HttpSource::create(Request::METHOD_GET,
            sprintf('purchasing/wc_vendor_warranty_claims/%s/%s_files/%s',
                $identifier['resource_id'],
                $identifier['type'] ?? 'wc',
                $identifier['file_id']
            )
        );
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
