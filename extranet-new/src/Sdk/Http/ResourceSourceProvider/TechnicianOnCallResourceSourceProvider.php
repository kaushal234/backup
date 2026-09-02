<?php

declare(strict_types=1);

namespace App\Sdk\Http\ResourceSourceProvider;

use App\CQRS\Command\User\FileCommandInterface;
use App\Sdk\Http\HttpSource;
use App\Sdk\Resource\TechnicianOnCall;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;

class TechnicianOnCallResourceSourceProvider extends AbstractResourceSourceProvider
{
    public const array NORMALIZATION_GROUPS = [
        'equipment_record',
        'extranet_user',
        'user_profile',
        'user_profile:detail',
        'country',
        'extranet_user_detail',
        'address',
        'expose_legacy',
        'equipment_serial',
    ];

    public function supports(string $resource): bool
    {
        return TechnicianOnCall::class === $resource;
    }

    /**
     * @param string|array<string, string> $identifier
     */
    public function getFindSource(array|string $identifier): ?HttpSource
    {
        if (isset($identifier['token'])) {
            return HttpSource::create(
                Request::METHOD_GET,
                \sprintf('%s/%d/from_token?token=%s', $this->getResourceIri(), $identifier['resource_id'], $identifier['token']),
                ['query' => ['normalizationGroups' => self::NORMALIZATION_GROUPS]]
            );
        }

        return HttpSource::create(
            Request::METHOD_GET,
            \sprintf('%s/%s', $this->getResourceIri(), $identifier['resource_id']),
            ['query' => ['normalizationGroups' => self::NORMALIZATION_GROUPS]]
        );
    }

    /**
     * @param string|array<string, string> $identifier
     * @param array<string, mixed>         $payload
     */
    public function getUpdateSource(array|string $identifier, array $payload): ?HttpSource
    {
        return null;
    }

    public function getUploadSource(FileCommandInterface $fileCommand): ?HttpSource
    {
        $formData = new FormDataPart(
            array_filter([
                'file' => DataPart::fromPath(
                    $fileCommand->file->getPathname(),
                    $fileCommand->file->getClientOriginalName()
                ),
                'public' => 'true',
                'description' => $fileCommand->description,
            ], static fn (mixed $value): bool => null !== $value)
        );

        $uri = \sprintf('%s/%s/files', $this->getResourceIri(), $fileCommand->id);

        return HttpSource::create(Request::METHOD_POST, $uri, [
            'headers' => $formData->getPreparedHeaders()->toArray() + ['Accept' => 'application/ld+json'],
            'body' => $formData->bodyToIterable(),
        ]);
    }

    public function getPaginationSource(int $page, int $itemsPerPage, array $criteria = []): ?HttpSource
    {
        return parent::getPaginationSource($page, $itemsPerPage, [
            ...$criteria,
            ...['normalizationGroups' => self::NORMALIZATION_GROUPS],
        ]);
    }

    public function getResourceIri(): string
    {
        return 'service/technician_on_calls';
    }
}
