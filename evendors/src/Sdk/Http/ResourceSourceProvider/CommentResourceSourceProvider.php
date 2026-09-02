<?php

declare(strict_types=1);

namespace App\Sdk\Http\ResourceSourceProvider;

use App\Sdk\Http\HttpSource;
use App\Sdk\Internal\Utility;
use App\Sdk\Resource\Comment;
use Symfony\Component\HttpFoundation\Request;

use function sprintf;

/**
 * @implements ResourceSourceProviderInterface<Comment>
 */
final class CommentResourceSourceProvider implements ResourceSourceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function supports(string $resource): bool
    {
        return Comment::class === $resource;
    }

    /**
     * {@inheritDoc}
     */
    public function getFindSource(string|array $identifier): HttpSource
    {
        return HttpSource::create(Request::METHOD_GET, Utility::buildIri('/comments/%s', $identifier));
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
        return HttpSource::create(Request::METHOD_GET, sprintf('comments/%s/files/%s', $identifier['resource_id'], $identifier['file_id']));
    }

    /**
     * {@inheritDoc}
     */
    public function getFindAllSource(array $criteria = []): HttpSource
    {
        return HttpSource::create(Request::METHOD_GET, '/comments', [
            'query' => $criteria,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getPaginationSource(int $page, int $itemsPerPage, array $criteria = []): HttpSource
    {
        return HttpSource::create(Request::METHOD_GET, '/dms', [
            'query' => $criteria + [
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
