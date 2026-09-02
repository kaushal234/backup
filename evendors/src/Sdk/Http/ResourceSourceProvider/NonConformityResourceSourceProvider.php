<?php

declare(strict_types=1);

namespace App\Sdk\Http\ResourceSourceProvider;

use App\Sdk\Http\DistributedHttpSource;
use App\Sdk\Http\HttpSource;
use App\Sdk\Internal\Utility;
use App\Sdk\Resource\NonConformity;
use App\Security\Security;
use Symfony\Component\HttpFoundation\Request;

use function sprintf;

/**
 * @implements ResourceSourceProviderInterface<NonConformity>
 */
class NonConformityResourceSourceProvider implements ResourceSourceProviderInterface
{
    public function __construct(
        protected readonly Security $security,
    ) {
    }

    public function supports(string $resource): bool
    {
        return NonConformity::class === $resource;
    }

    public function getFindSource(string|array $identifier): ?HttpSource
    {
        return HttpSource::create(Request::METHOD_GET, Utility::buildIri('/quality/non_conformities/%s', $identifier));
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
        return HttpSource::create(Request::METHOD_GET, sprintf('quality/non_conformities/%s/files/%s', $identifier['resource_id'], $identifier['file_id']));
    }

    public function getFindAllSource(array $criteria = []): ?DistributedHttpSource
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
