<?php

declare(strict_types=1);

namespace App\Sdk;

use App\Sdk\DataTransformer\DataTransformerInterface;
use App\Sdk\Exception\UnsupportedOperationException;
use App\Sdk\Http\DistributedHttpSource;
use App\Sdk\Http\HttpSource;
use App\Sdk\Http\SourceProviderInterface;
use App\Sdk\Resource\ResourceInterface;
use App\Sdk\TokenProvider\TokenProviderInterface;
use Psl\Collection\AccessibleCollectionInterface;
use Psl\Collection\MutableVector;
use Psl\File;
use Psl\Filesystem;
use Psl\Iter;
use Psl\Vec;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

final class Client implements ClientInterface
{
    public function __construct(
        private readonly Http\ClientInterface $http,
        private readonly TokenProviderInterface $tokenProvider,
        private readonly DataTransformerInterface $dataTransformer,
        private readonly SourceProviderInterface $sourceProvider,
    ) {
    }

    /**
     * @template T of ResourceInterface
     *
     * @param class-string<T> $resource
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
    public function find(string $resource, string|array $identifier): ResourceInterface
    {
        $sources = $this->sourceProvider->getFindSources($resource, $identifier);
        if ([] === $sources) {
            throw UnsupportedOperationException::forFinding($resource);
        }

        $response = $this->requestUsingSources($sources);

        return $this->dataTransformer->transform($resource, $response);
    }

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
    public function update(string $resource, array|string $identifier, array $update): array
    {
        $sources = $this->sourceProvider->getUpdateSources($resource, $identifier, $update);
        if ([] === $sources) {
            throw UnsupportedOperationException::forUpdating($resource);
        }

        return $this->requestUsingSources($sources);
    }

    /**
     * @template T of ResourceInterface
     *
     * @param class-string<T> $resource
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws UnsupportedOperationException
     */
    public function download(string $resource, string|array $identifier): DownloadedFile
    {
        $sources = $this->sourceProvider->getDownloadSources($resource, $identifier);
        if ([] === $sources) {
            throw UnsupportedOperationException::forDownload($resource);
        }

        return $this->downloadUsingSources($sources);
    }

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
    public function downloadExcel(string $resource, array $criteria = []): DownloadedFile
    {
        $sources = $this->sourceProvider->getExcelSources($resource, $criteria);
        if ([] === $sources) {
            throw UnsupportedOperationException::forDownload($resource);
        }

        return $this->downloadUsingSources($sources);
    }

    /**
     * @template T of ResourceInterface
     *
     * @param class-string<T>      $resource
     * @param array<string, mixed> $criteria
     *
     * @return AccessibleCollectionInterface<T>
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws UnsupportedOperationException
     */
    public function findAll(string $resource, array $criteria = []): AccessibleCollectionInterface
    {
        $sources = $this->sourceProvider->getFindAllSources($resource, $criteria);
        if ([] === $sources) {
            throw UnsupportedOperationException::forListing($resource);
        }

        /** @var MutableVector<T> $collection */
        $collection = new MutableVector([]);
        Iter\apply(
            Vec\map(
                $this->requestUsingDistributedSources($sources),
                fn (array $response): array => $this->dataTransformer->transformCollection($resource, $response)->toArray()
            ),

            /**
             * @param list<T> $resource
             */
            static function (array $resource) use ($collection): void {
                $collection->addAll($resource);
            }
        );

        return $collection;
    }

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
    public function paginate(string $resource, int $page, int $itemsPerPage = self::DEFAULT_ITEMS_PER_PAGE, array $criteria = []): Page
    {
        $sources = $this->sourceProvider->getPaginationSources($resource, $page, $itemsPerPage, $criteria);
        if ([] === $sources) {
            throw UnsupportedOperationException::forPagination($resource);
        }

        $response = $this->requestUsingSources($sources);

        return $this->dataTransformer->transformPage($resource, $response, $page, $itemsPerPage);
    }

    /**
     * @param non-empty-list<HttpSource> $sources
     *
     * @return array<string, mixed>
     *
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function requestUsingSources(array $sources): array
    {
        $token = $this->tokenProvider->getToken();
        $client = $this->http->withOptions(['auth_bearer' => $token]);

        $lastException = null;
        foreach ($client->concurrent(...$sources) as $response) {
            try {
                return $response->toArray();
            } catch (ServerExceptionInterface|ClientExceptionInterface $e) {
                $lastException = $e;
            }
        }

        throw $lastException;
    }

    /**
     * @param non-empty-list<HttpSource|DistributedHttpSource> $sources
     *
     * @return list<array<string, mixed>>
     *
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function requestUsingDistributedSources(array $sources): array
    {
        $token = $this->tokenProvider->getToken();
        $client = $this->http->withOptions(['auth_bearer' => $token]);

        [$distributedSources, $nonDistributedSources] = Vec\partition(
            $sources,
            static fn (HttpSource|DistributedHttpSource $source): bool => $source instanceof DistributedHttpSource,
        );

        // try all non-distributed sources first, as this usually results
        // in less HTTP requests.
        $lastException = null;
        foreach ($client->concurrent(...$nonDistributedSources) as $response) {
            try {
                return [$response->toArray()];
            } catch (ServerExceptionInterface|ClientExceptionInterface $e) {
                $lastException = $e;
            }
        }

        foreach ($client->concurrentDistribution(...$distributedSources) as $responses) {
            try {
                $result = [];
                foreach ($responses as $response) {
                    $result[] = $response->toArray();
                }

                return $result;
            } catch (ServerExceptionInterface|ClientExceptionInterface $e) {
                $lastException = $e;
            }
        }

        throw $lastException;
    }

    /**
     * @param non-empty-list<HttpSource> $sources
     *
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    private function downloadUsingSources(array $sources): DownloadedFile
    {
        $token = $this->tokenProvider->getToken();
        $client = $this->http->withOptions(['auth_bearer' => $token]);

        $lastException = null;
        foreach ($client->concurrent(...$sources) as $response) {
            try {
                $status = $response->getStatusCode();
                $headers = $response->getHeaders();
                $content = $response->getContent();

                $filename = Filesystem\create_temporary_file(prefix: 'evendors-sdk');
                File\write($filename, $content);

                return new DownloadedFile($status, $headers, File\open_read_only($filename));
            } catch (ServerExceptionInterface|ClientExceptionInterface $e) {
                $lastException = $e;
            }
        }

        throw $lastException;
    }
}
