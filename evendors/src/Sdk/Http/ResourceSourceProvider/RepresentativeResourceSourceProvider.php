<?php

declare(strict_types=1);

namespace App\Sdk\Http\ResourceSourceProvider;

use App\Sdk\Http\HttpSource;
use App\Sdk\Resource\Representative;
use App\Security\Security;
use App\Security\User\User;
use Symfony\Component\HttpFoundation\Request;

/**
 * @implements ResourceSourceProviderInterface<Representative>
 */
final class RepresentativeResourceSourceProvider implements ResourceSourceProviderInterface
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    public function supports(string $resource): bool
    {
        return Representative::class === $resource;
    }

    public function getFindAllSource(array $criteria = []): ?HttpSource
    {
        $user = $this->security->getAuthenticatedUser();
        if (!$user instanceof User) {
            return null;
        }

        return HttpSource::create(Request::METHOD_GET, '/buyers', ['query' => $criteria]);
    }

    public function getFindSource(string|array $identifier): ?HttpSource
    {
        return null;
    }

    public function getUpdateSource(array|string $identifier, array $update): ?HttpSource
    {
        return null;
    }

    public function getDownloadSource(array|string $identifier): ?HttpSource
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
