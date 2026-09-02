<?php

declare(strict_types=1);

namespace App\Sdk\Http\ResourceSourceProvider;

use App\Sdk\Http\HttpSource;
use App\Sdk\Resource\TechnicianOnCallSurvey;
use Symfony\Component\HttpFoundation\Request;

class TechnicianOnCallSatisfactionResourceSourceProvider extends AbstractResourceSourceProvider
{
    public function supports(string $resource): bool
    {
        return TechnicianOnCallSurvey::class === $resource;
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
        return null;
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
        return null;
    }

    /**
     * @param array<string, mixed> $criteria
     */
    public function getFindAllSource(array $criteria = []): ?HttpSource
    {
        return null;
    }

    public function getResourceIri(): string
    {
        return 'service/technician_on_call_surveys';
    }

    public function getPaginationSource(int $page, int $itemsPerPage, array $criteria = []): ?HttpSource
    {
        return null;
    }
}
