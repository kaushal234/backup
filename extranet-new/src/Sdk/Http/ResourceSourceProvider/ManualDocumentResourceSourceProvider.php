<?php

declare(strict_types=1);

namespace App\Sdk\Http\ResourceSourceProvider;

use App\Sdk\Http\HttpSource;
use App\Sdk\Resource\ManualDocument;
use Symfony\Component\HttpFoundation\Request;

class ManualDocumentResourceSourceProvider extends AbstractResourceSourceProvider
{
    public function supports(string $resource): bool
    {
        return ManualDocument::class === $resource;
    }

    /**
     * @param string|array<string, string> $identifier
     */
    public function getDownloadSource(array|string $identifier): ?HttpSource
    {
        $uri = ($identifier['extension'] ?? null) === 'pdf' ? \sprintf('%s/%s/files/%s', $this->getResourceIri(), $identifier['resource_id'], $identifier['file_id']) : \sprintf('%s/%s/pdf/document', $this->getResourceIri(), $identifier['resource_id']);
        $accept = ($identifier['extension'] ?? null) === 'pdf' ? 'application/ld+json' : 'application/pdf';

        return HttpSource::create(Request::METHOD_GET, $uri, ['headers' => ['Accept' => $accept]]);
    }

    public function getResourceIri(): string
    {
        return 'support/manual_documents';
    }
}
