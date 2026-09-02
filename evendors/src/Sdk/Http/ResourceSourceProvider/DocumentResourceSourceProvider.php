<?php

declare(strict_types=1);

namespace App\Sdk\Http\ResourceSourceProvider;

use App\Sdk\Http\HttpSource;
use App\Sdk\Internal\Utility;
use App\Sdk\Resource\Document;
use Symfony\Component\HttpFoundation\Request;

/**
 * @implements ResourceSourceProviderInterface<Document>
 */
final class DocumentResourceSourceProvider implements ResourceSourceProviderInterface
{
    private const PORTAL = 'EVENDOR';

    /**
     * {@inheritDoc}
     */
    public function supports(string $resource): bool
    {
        return Document::class === $resource;
    }

    /**
     * {@inheritDoc}
     */
    public function getFindSource(string|array $identifier): HttpSource
    {
        return HttpSource::create(Request::METHOD_GET, Utility::buildIri('/dms/%s', $identifier));
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
    public function getDownloadSource(string|array $identifier): HttpSource
    {
        return HttpSource::create(Request::METHOD_GET, Utility::buildIri('/dms/%s', $identifier), [
            'headers' => [
                'Accept' => 'application/vnd.alvest.dms',
            ],
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getFindAllSource(array $criteria = []): HttpSource
    {
        return HttpSource::create(Request::METHOD_GET, '/dms', [
            'query' => [
                'portal' => self::PORTAL,
                'status' => ['ACTIVE', 'EXPIRED', 'REVISION', 'APPROVAL'],
            ],
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getPaginationSource(int $page, int $itemsPerPage, array $criteria = []): HttpSource
    {
        return HttpSource::create(Request::METHOD_GET, '/dms', [
            'query' => [
                'portal' => self::PORTAL,
                'pagination' => true,
                'page' => $page,
                'itemsPerPage' => $itemsPerPage,
            ],
        ]);
    }

    public function getExcelSource(array $criteria = []): ?HttpSource
    {
        return null;
    }
}
