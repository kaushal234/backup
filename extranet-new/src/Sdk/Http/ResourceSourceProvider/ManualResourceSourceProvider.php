<?php

declare(strict_types=1);

namespace App\Sdk\Http\ResourceSourceProvider;

use App\Sdk\Http\HttpSource;
use App\Sdk\Resource\Manual;
use Symfony\Component\HttpFoundation\Request;

class ManualResourceSourceProvider extends AbstractResourceSourceProvider
{
    public function supports(string $resource): bool
    {
        return Manual::class === $resource;
    }

    /**
     * @param string|array<string, string> $identifier
     */
    public function getDownloadSource(array|string $identifier): ?HttpSource
    {
        $uri = \sprintf('%s/%s/pdf/chapter4', $this->getResourceIri(), $identifier['resource_id']);

        return HttpSource::create(Request::METHOD_GET, $uri, ['headers' => ['Accept' => 'application/pdf']]);
    }

    public function getResourceIri(): string
    {
        return 'support/manuals';
    }
}
