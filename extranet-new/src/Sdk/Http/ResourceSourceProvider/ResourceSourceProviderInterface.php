<?php

declare(strict_types=1);

namespace App\Sdk\Http\ResourceSourceProvider;

use App\CQRS\Command\User\FileCommandInterface;
use App\Sdk\Http\HttpSource;
use App\Sdk\Resource\ResourceInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * @template T of ResourceInterface
 */
#[AutoconfigureTag(name: 'resource.source_provider')]
interface ResourceSourceProviderInterface
{
    public function supports(string $resource): bool;

    /**
     * @param string|array<string, string> $identifier
     */
    public function getFindSource(string|array $identifier): ?HttpSource;

    /**
     * @param string|array<string, string> $identifier
     */
    public function getDownloadSource(string|array $identifier): ?HttpSource;

    public function getUploadSource(FileCommandInterface $fileCommand): ?HttpSource;

    /**
     * @param array<string, mixed> $criteria
     */
    public function getFindAllSource(array $criteria = []): ?HttpSource;

    /**
     * @param array<string, mixed> $payload
     */
    public function getCreateSource(array $payload): ?HttpSource;

    /**
     * @param string|array<string, string> $identifier
     * @param array<string, mixed>         $payload
     */
    public function getUpdateSource(string|array $identifier, array $payload): ?HttpSource;

    /**
     * @param positive-int            $page
     * @param positive-int            $itemsPerPage
     * @param array<array-key, mixed> $criteria
     */
    public function getPaginationSource(int $page, int $itemsPerPage, array $criteria = []): ?HttpSource;

    /**
     * @param array<array-key, mixed> $criteria
     */
    public function getExportSource(string $format, array $criteria = []): ?HttpSource;

    public function getResourceIri(): string;
}
