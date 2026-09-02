<?php

declare(strict_types=1);

namespace App\Sdk\Http\ResourceSourceProvider;

use App\CQRS\Command\User\FileCommandInterface;
use App\Sdk\Http\HttpSource;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;

abstract class AbstractResourceSourceProvider implements ResourceSourceProviderInterface
{
    /**
     * @param string|array<string, string> $identifier
     */
    public function getFindSource(array|string $identifier): ?HttpSource
    {
        return HttpSource::create(
            Request::METHOD_GET,
            \sprintf('%s/%s', $this->getResourceIri(), $identifier['resource_id'])
        );
    }

    /**
     * @param string|array<string, string> $identifier
     */
    public function getDownloadSource(array|string $identifier): ?HttpSource
    {
        $uri = \sprintf(
            '%s/%s/files/%s',
            $this->getResourceIri(),
            $identifier['resource_id'],
            $identifier['file_id']
        );

        return HttpSource::create(Request::METHOD_GET, $uri);
    }

    public function getUploadSource(FileCommandInterface $fileCommand): ?HttpSource
    {
        $formData = new FormDataPart([
            'file' => DataPart::fromPath(
                $fileCommand->file->getPathname(),
                $fileCommand->file->getClientOriginalName()
            ),
            'public' => 'true',
        ]);

        $uri = \sprintf('%s/%s/files', $this->getResourceIri(), $fileCommand->id);

        return HttpSource::create(Request::METHOD_POST, $uri, [
            'headers' => $formData->getPreparedHeaders()->toArray() + ['Accept' => 'application/ld+json'],
            'body' => $formData->bodyToIterable(),
        ]);
    }

    /**
     * @param array<string, mixed> $criteria
     */
    public function getFindAllSource(array $criteria = []): ?HttpSource
    {
        return HttpSource::create(Request::METHOD_GET, $this->getResourceIri(), $criteria);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function getCreateSource(array $payload): ?HttpSource
    {
        return HttpSource::create(Request::METHOD_POST, $this->getResourceIri(), [
            'json' => $payload,
        ]);
    }

    /**
     * @param string|array<string, string> $identifier
     * @param array<string, mixed>         $payload
     */
    public function getUpdateSource(array|string $identifier, array $payload): ?HttpSource
    {
        $uri = \sprintf('%s/%s', $this->getResourceIri(), $identifier['resource_id']);

        return HttpSource::create(Request::METHOD_PUT, $uri, [
            'json' => $payload,
        ]);
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

    /**
     * @param array<string, mixed> $criteria
     */
    public function getExportSource(string $format, array $criteria = []): ?HttpSource
    {
        return HttpSource::create(Request::METHOD_GET, $this->getResourceIri(), [
            'headers' => [
                'Accept' => $format,
            ],
            'query' => [
                'pagination' => false,
                ...$criteria,
            ],
        ]);
    }
}
