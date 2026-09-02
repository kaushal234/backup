<?php

declare(strict_types=1);

namespace App\Sdk\Http\ResourceSourceProvider;

use App\Sdk\Http\HttpSource;
use App\Sdk\Resource\ServiceBulletin;
use Symfony\Component\HttpFoundation\Request;

class ServiceBulletinResourceSourceProvider extends AbstractResourceSourceProvider
{
    public function supports(string $resource): bool
    {
        return ServiceBulletin::class === $resource;
    }

    /**
     * @param string|array<string, string|int|null> $identifier
     */
    public function getFindSource(array|string $identifier): ?HttpSource
    {
        $options = [];
        if (\is_array($identifier) && null !== ($identifier['customerLegacyId'] ?? null)) {
            $options['query'] = ['customer' => $identifier['customerLegacyId']];
        }

        return HttpSource::create(Request::METHOD_GET, \sprintf('%s/%s', $this->getResourceIri(), $identifier['resource_id']), $options);
    }

    /**
     * @param string|array<string, string> $identifier
     */
    public function getDownloadSource(array|string $identifier): ?HttpSource
    {
        return null;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function getCreateSource(array $payload): ?HttpSource
    {
        return null;
    }

    /**
     * @param string|array<string, string> $identifier
     * @param array<string, mixed>         $payload
     */
    public function getUpdateSource(array|string $identifier, array $payload): ?HttpSource
    {
        return null;
    }

    /**
     * @param array<string, mixed> $criteria
     */
    public function getFindAllSource(array $criteria = []): ?HttpSource
    {
        return HttpSource::create(Request::METHOD_GET, $this->getResourceIri(), $criteria);
    }

    public function getPaginationSource(int $page, int $itemsPerPage, array $criteria = []): ?HttpSource
    {
        return HttpSource::create(Request::METHOD_GET, $this->getResourceIri(), [
            'query' => [
                'pagination' => true,
                'page' => $page,
                'itemsPerPage' => $itemsPerPage,
                ...$criteria,
            ],
        ]);
    }

    public function getResourceIri(): string
    {
        return 'service_bulletins';
    }
}
