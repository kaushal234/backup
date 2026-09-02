<?php

declare(strict_types=1);

namespace App\Sdk\Http\ResourceSourceProvider;

use App\CQRS\Command\User\FileCommandInterface;
use App\Sdk\Http\HttpSource;
use App\Sdk\Resource\ServiceBulletinFile;
use Symfony\Component\HttpFoundation\Request;

class ServiceBulletinFileResourceSourceProvider extends AbstractResourceSourceProvider
{
    public function supports(string $resource): bool
    {
        return ServiceBulletinFile::class === $resource;
    }

    public function getFindAllSource(array $criteria = []): ?HttpSource
    {
        $url = $this->getResourceIri();
        if (isset($criteria['query']['id'])) {
            $url = \sprintf('%s/%s', $url, $criteria['query']['id']);
            unset($criteria['query']['id']);
        }

        return HttpSource::create(Request::METHOD_GET, $url, $criteria);
    }

    public function getDownloadSource(array|string $identifier): ?HttpSource
    {
        $uri = \sprintf(
            '%s/%s/download',
            $this->getResourceIri(),
            $identifier['id'],
        );

        return HttpSource::create(Request::METHOD_GET, $uri);
    }

    public function getUpdateSource(array|string $identifier, array $payload): ?HttpSource
    {
        return null;
    }

    public function getUploadSource(FileCommandInterface $fileCommand): ?HttpSource
    {
        return null;
    }

    public function getCreateSource(array $payload): ?HttpSource
    {
        return null;
    }

    public function getPaginationSource(int $page, int $itemsPerPage, array $criteria = []): ?HttpSource
    {
        return null;
    }

    public function getResourceIri(): string
    {
        return 'legacy/service_bulletin_files';
    }
}
