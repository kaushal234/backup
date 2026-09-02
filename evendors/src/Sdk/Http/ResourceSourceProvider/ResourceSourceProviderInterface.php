<?php

declare(strict_types=1);

namespace App\Sdk\Http\ResourceSourceProvider;

use App\Sdk\Http\DistributedHttpSource;
use App\Sdk\Http\HttpSource;
use App\Sdk\Resource\ResourceInterface;

/**
 * @template T of ResourceInterface
 */
interface ResourceSourceProviderInterface
{
    /**
     * @template Ts of ResourceInterface
     *
     * @param class-string<Ts> $resource
     *
     * @return bool - True, if the factory supports creating requests for the given resource, false otherwise
     *
     * @psalm-assert-if-true class-string<T> $resource
     */
    public function supports(string $resource): bool;

    /**
     * Retrieve an {@see HttpSource} instance for finding the resource identified with `$identifier`.
     *
     * @param string|array<string, string> $identifier
     */
    public function getFindSource(string|array $identifier): ?HttpSource;

    /**
     * Retrieve an {@see HttpSource} instance for updating the given resource identified with `$identifier`.
     *
     * @param string|array<string, string> $identifier
     * @param array<string, mixed>         $update
     */
    public function getUpdateSource(string|array $identifier, array $update): ?HttpSource;

    /**
     * Retrieve an {@see HttpSource} instance for downloading the resource identified with `$identifier`.
     *
     * @param string|array<string, string> $identifier
     */
    public function getDownloadSource(string|array $identifier): ?HttpSource;

    /**
     * Retrieve an {@see HttpSource}, or {@see DistributedHttpSource} instance for finding all instances of the supported resource.
     *
     * @param array<array-key, mixed> $criteria
     */
    public function getFindAllSource(array $criteria = []): DistributedHttpSource|HttpSource|null;

    /**
     * Retrieve an {@see HttpSource} instance for paginating the supported resource.
     *
     * @param positive-int            $page
     * @param positive-int            $itemsPerPage
     * @param array<array-key, mixed> $criteria
     */
    public function getPaginationSource(int $page, int $itemsPerPage, array $criteria = []): ?HttpSource;

    /**
     * Retrieve an {@see HttpSource} instance for downloading excel file for the supported resource.
     *
     * @param array<array-key, mixed> $criteria
     */
    public function getExcelSource(array $criteria = []): ?HttpSource;
}
