<?php

declare(strict_types=1);

namespace App\Sdk\Http\ResourceSourceProvider;

use App\Sdk\Http\HttpSource;
use App\Sdk\Internal\Utility;
use App\Sdk\Resource\Supplier;
use App\Security\Security;
use App\Security\User\User;
use Symfony\Component\HttpFoundation\Request;

/**
 * @implements ResourceSourceProviderInterface<Supplier>
 */
final class SupplierResourceSourceProvider implements ResourceSourceProviderInterface
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    public function supports(string $resource): bool
    {
        return Supplier::class === $resource;
    }

    public function getFindSource(string|array $identifier): ?HttpSource
    {
        return HttpSource::create(Request::METHOD_GET, Utility::buildIri('/ion/business_partners/%s', $identifier['code']));
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
        $user = $this->security->getAuthenticatedUser();
        if (!$user instanceof User) {
            return null;
        }

        return HttpSource::create(Request::METHOD_GET, '/ion/business_partners');
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
