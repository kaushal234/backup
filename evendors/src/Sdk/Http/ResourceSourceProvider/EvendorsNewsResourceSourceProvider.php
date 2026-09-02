<?php

declare(strict_types=1);

namespace App\Sdk\Http\ResourceSourceProvider;

use App\Sdk\Http\HttpSource;
use App\Sdk\Resource\EvendorsNews;
use App\Security\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @implements ResourceSourceProviderInterface<EvendorsNews>
 */
class EvendorsNewsResourceSourceProvider implements ResourceSourceProviderInterface
{
    public function __construct(
        protected readonly Security $security,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function supports(string $resource): bool
    {
        return EvendorsNews::class === $resource;
    }

    /**
     * {@inheritDoc}
     */
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

    /**
     * {@inheritDoc}
     */
    public function getDownloadSource(string|array $identifier): ?HttpSource
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getFindAllSource(array $criteria = []): ?HttpSource
    {
        return HttpSource::create(Request::METHOD_GET, '/evendors_news', [
            'query' => $criteria,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getPaginationSource(int $page, int $itemsPerPage, array $criteria = []): ?HttpSource
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getExcelSource(array $criteria = []): ?HttpSource
    {
        return null;
    }
}
