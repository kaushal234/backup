<?php

declare(strict_types=1);

namespace App\Sdk\Http;

use App\Sdk\Exception\NoSupportiveResourceSourceProviderException;
use App\Sdk\Resource\ResourceInterface;

interface SourceProviderInterface
{
    /**
     * Retrieve a list of {@see HttpSource} instances for finding the given resource.
     *
     * @template T of ResourceInterface
     *
     * @param class-string<T>              $resource
     * @param string|array<string, string> $identifier
     *
     * @return list<HttpSource>
     *
     * @throws NoSupportiveResourceSourceProviderException if no compatible resource provider is found
     */
    public function getFindSources(string $resource, string|array $identifier): array;

    /**
     * Retrieve a list of {@see HttpSource} instances for updating the given resource.
     *
     * @template T of ResourceInterface
     *
     * @param class-string<T>              $resource
     * @param string|array<string, string> $identifier
     * @param array<string, mixed>         $update
     *
     * @return list<HttpSource>
     *
     * @throws NoSupportiveResourceSourceProviderException if no compatible resource provider is found
     */
    public function getUpdateSources(string $resource, array|string $identifier, array $update): array;

    /**
     * Retrieve a list of {@see HttpSource} instances for downloading the given resource identified with `$id`.
     *
     * @template T of ResourceInterface
     *
     * @param class-string<T>              $resource
     * @param string|array<string, string> $identifier
     *
     * @return list<HttpSource>
     *
     * @throws NoSupportiveResourceSourceProviderException if no compatible resource provider is found
     */
    public function getDownloadSources(string $resource, string|array $identifier): array;

    /**
     * Retrieve a list of {@see HttpSource} instances for finding all instances of the given resource.
     *
     * @template T of ResourceInterface
     *
     * @param class-string<T>      $resource
     * @param array<string, mixed> $criteria
     *
     * @phpstan-return list<HttpSource|DistributedHttpSource>
     *
     * @psalm-return list<HttpSource|DistributedHttpSource>
     *
     * @throws NoSupportiveResourceSourceProviderException if no compatible resource provider is found
     */
    public function getFindAllSources(string $resource, array $criteria = []): array;

    /**
     * Retrieve a list of {@see HttpSource} instances for paginating the given resource.
     *
     * @template T of ResourceInterface
     *
     * @param class-string<T>      $resource
     * @param positive-int         $page
     * @param positive-int         $itemsPerPage
     * @param array<string, mixed> $criteria
     *
     * @return list<HttpSource>
     *
     * @throws NoSupportiveResourceSourceProviderException if no compatible resource provider is found
     */
    public function getPaginationSources(string $resource, int $page, int $itemsPerPage, array $criteria = []): array;

    /**
     * Retrieve a list of {@see HttpSource} instances for downloading excel file for the given resource.
     *
     * @template T of ResourceInterface
     *
     * @param class-string<T>      $resource
     * @param array<string, mixed> $criteria
     *
     * @return list<HttpSource>
     *
     * @throws NoSupportiveResourceSourceProviderException if no compatible resource provider is found
     */
    public function getExcelSources(string $resource, array $criteria = []): array;
}
