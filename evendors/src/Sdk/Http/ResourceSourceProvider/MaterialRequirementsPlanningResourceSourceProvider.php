<?php

declare(strict_types=1);

namespace App\Sdk\Http\ResourceSourceProvider;

use App\Sdk\Http\DistributedHttpSource;
use App\Sdk\Http\HttpSource;
use App\Sdk\Resource\MaterialRequirementsPlanning;
use App\Security\Security;
use App\Security\User\User;
use Symfony\Component\HttpFoundation\Request;

/**
 * @implements ResourceSourceProviderInterface<MaterialRequirementsPlanning>
 */
final class MaterialRequirementsPlanningResourceSourceProvider implements ResourceSourceProviderInterface
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    public function supports(string $resource): bool
    {
        return MaterialRequirementsPlanning::class === $resource;
    }

    public function getFindSource(array|string $identifier): ?HttpSource
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

    public function getFindAllSource(array $criteria = []): ?DistributedHttpSource
    {
        $user = $this->security->getAuthenticatedUser();
        if (!$user instanceof User) {
            return null;
        }

        return DistributedHttpSource::combine(HttpSource::create(Request::METHOD_GET, '/ion/planned_orders'));
    }

    public function getPaginationSource(int $page, int $itemsPerPage, array $criteria = []): ?HttpSource
    {
        return null;
    }

    public function getExcelSource(array $criteria = []): ?HttpSource
    {
        $user = $this->security->getAuthenticatedUser();
        if (!$user instanceof User) {
            return null;
        }

        return HttpSource::create(Request::METHOD_GET, '/ion/planned_orders', [
            'query' => $criteria,
            'headers' => ['Accept' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        ]);
    }
}
