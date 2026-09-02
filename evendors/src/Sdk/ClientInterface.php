<?php

declare(strict_types=1);

namespace App\Sdk;

use App\Sdk\Exception\UnsupportedOperationException;
use App\Sdk\Resource\ResourceInterface;
use Psl\Collection\AccessibleCollectionInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

interface ClientInterface
{
    public const DEFAULT_ITEMS_PER_PAGE = 50;

    /**
     * @template T of ResourceInterface
     *
     * @param class-string<T>                       $resource
     * @param string|array<string, int|string|null> $identifier
     *
     * @return T
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws UnsupportedOperationException
     */
    public function find(string $resource, string|array $identifier): ResourceInterface;

    /**
     * @template T of ResourceInterface
     *
     * @param class-string<T>              $resource
     * @param string|array<string, string> $identifier
     * @param array<array-key, mixed>      $update
     *
     * @return array<array-key, mixed>
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws UnsupportedOperationException
     */
    public function update(string $resource, string|array $identifier, array $update): array;

    /**
     * @template T of ResourceInterface
     *
     * @param class-string<T>              $resource
     * @param string|array<string, string> $identifier
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws UnsupportedOperationException
     */
    public function download(string $resource, string|array $identifier): DownloadedFile;

    /**
     * @template T of ResourceInterface
     *
     * @param class-string<T>      $resource
     * @param array<string, mixed> $criteria
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws UnsupportedOperationException
     */
    public function downloadExcel(string $resource, array $criteria = []): DownloadedFile;

    /**
     * @template T of ResourceInterface
     *
     * @param class-string<T>      $resource
     * @param array<string, mixed> $criteria
     *
     * @return AccessibleCollectionInterface<int, T>
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws UnsupportedOperationException
     */
    public function findAll(string $resource, array $criteria = []): AccessibleCollectionInterface;

    /**
     * @template T of ResourceInterface
     *
     * @param class-string<T>      $resource
     * @param array<string, mixed> $criteria
     *
     * @return Page<T>
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws UnsupportedOperationException
     */
    public function paginate(string $resource, int $page, int $itemsPerPage = self::DEFAULT_ITEMS_PER_PAGE, array $criteria = []): Page;
}
