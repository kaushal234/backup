<?php

declare(strict_types=1);

namespace App\Sdk\Http\ResourceSourceProvider;

use App\Sdk\Http\HttpSource;
use App\Sdk\Resource\ManualDocumentPublic;
use Symfony\Component\HttpFoundation\Request;

class ManualDocumentPublicResourceSourceProvider extends AbstractResourceSourceProvider
{
    public function __construct(
        private readonly string $publicKey,
    ) {
    }

    public function supports(string $resource): bool
    {
        return ManualDocumentPublic::class === $resource;
    }

    /**
     * @param string|array<string, string> $identifier
     */
    public function getFindSource(array|string $identifier): ?HttpSource
    {
        return null;
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

        return HttpSource::create(Request::METHOD_GET, $uri, [
            'auth_bearer' => $this->publicKey,
        ]);
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

    public function getResourceIri(): string
    {
        return '/support/manual_documents';
    }
}
