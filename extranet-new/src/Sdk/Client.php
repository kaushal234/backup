<?php

declare(strict_types=1);

namespace App\Sdk;

use App\CQRS\Command\User\FileCommandInterface;
use App\Sdk\DataTransformer\DataTransformer;
use App\Sdk\Exception\UnsupportedOperationException;
use App\Sdk\Http\Client as HttpClient;
use App\Sdk\Http\HttpSource;
use App\Sdk\Http\SourceProvider;
use App\Sdk\Resource\ResourceInterface;
use App\Security\User\User;
use Psl\Collection\AccessibleCollectionInterface;
use Psl\Collection\MutableVector;
use Psl\File;
use Psl\Filesystem;
use Psl\Iter;
use Psl\Vec;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class Client
{
    public const DEFAULT_ITEMS_PER_PAGE = 50;

    public function __construct(
        private readonly HttpClient $http,
        private readonly DataTransformer $dataTransformer,
        private readonly SourceProvider $sourceProvider,
        private readonly Security $security,
    ) {
    }

    /**
     * @param string|array<string, string> $identifier
     *
     * @throws ClientExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function download(string $resource, string|array $identifier): DownloadedFile
    {
        $source = $this->sourceProvider->getResourceSourceProvider($resource)->getDownloadSource($identifier);
        if (null === $source) {
            throw UnsupportedOperationException::forDownload($resource);
        }

        return $this->downloadUsingSources($source);
    }

    /**
     * @template T of ResourceInterface
     *
     * @param class-string<T> $resource
     *
     * @return array<array-key, mixed>
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function upload(string $resource, FileCommandInterface $fileCommand): array
    {
        $source = $this->sourceProvider->getResourceSourceProvider($resource)->getUploadSource($fileCommand);
        if (null === $source) {
            throw UnsupportedOperationException::forUpload($resource);
        }

        return $this->requestUsingSources($source);
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<array-key, mixed>
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws DecodingExceptionInterface
     */
    public function create(string $resource, array $payload): array
    {
        $source = $this->sourceProvider->getResourceSourceProvider($resource)->getCreateSource($payload);
        if (null === $source) {
            throw UnsupportedOperationException::forCreating($resource);
        }

        return $this->requestUsingSources($source);
    }

    /**
     * @template T of ResourceInterface
     *
     * @param class-string<T>           $resource
     * @param string|array<string, int> $identifier
     * @param array<array-key, mixed>   $payload
     *
     * @return array<array-key, mixed>
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function update(string $resource, string|array $identifier, array $payload): array
    {
        $source = $this->sourceProvider->getResourceSourceProvider($resource)->getUpdateSource($identifier, $payload);
        if (null === $source) {
            throw UnsupportedOperationException::forUpdating($resource);
        }

        return $this->requestUsingSources($source);
    }

    /**
     * @template T of ResourceInterface
     *
     * @param class-string<T>             $resource
     * @param string|array<string, mixed> $identifier
     *
     * @return T
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function find(string $resource, string|array $identifier): ResourceInterface
    {
        $source = $this->sourceProvider->getResourceSourceProvider($resource)->getFindSource($identifier);

        if (null === $source) {
            throw UnsupportedOperationException::forFinding($resource);
        }

        $response = $this->requestUsingSources($source);

        return $this->dataTransformer->transform($resource, $response);
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
     */
    public function findAll(string $resource, array $criteria = []): AccessibleCollectionInterface
    {
        $source = $this->sourceProvider->getResourceSourceProvider($resource)->getFindAllSource($criteria);
        if (null === $source) {
            throw UnsupportedOperationException::forListing($resource);
        }

        /** @var MutableVector<T> $collection */
        $collection = new MutableVector([]);
        Iter\apply(
            Vec\map(
                $this->requestUsingDistributedSources($source),
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
    public function paginate(string $resource, int $page, int $itemsPerPage = self::DEFAULT_ITEMS_PER_PAGE, array $criteria = []): PageInterface
    {
        $source = $this->sourceProvider->getResourceSourceProvider($resource)->getPaginationSource($page, $itemsPerPage, $criteria);
        if (null === $source) {
            throw UnsupportedOperationException::forPagination($resource);
        }

        $response = $this->requestUsingSources($source);

        return $this->dataTransformer->transformPage($resource, $response, $page, $itemsPerPage);
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
    public function export(string $resource, string $format, array $criteria = []): DownloadedFile
    {
        $source = $this->sourceProvider->getResourceSourceProvider($resource)->getExportSource($format, $criteria);
        if (null === $source) {
            throw UnsupportedOperationException::forExport($resource);
        }

        return $this->downloadUsingSources($source);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function requestUsingSources(HttpSource $source): array
    {
        $lastException = null;
        foreach ($this->getConfiguredClient($source)->concurrent($source) as $response) {
            try {
                return Response::HTTP_NO_CONTENT === $response->getStatusCode() ? [] : $response->toArray();
            } catch (ServerExceptionInterface|ClientExceptionInterface $e) {
                $lastException = $e;
            }
        }

        throw $lastException;
    }

    /**
     * @return list<array<string, mixed>>
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function requestUsingDistributedSources(HttpSource $source): array
    {
        $lastException = null;
        foreach ($this->getConfiguredClient($source)->concurrent($source) as $response) {
            try {
                return [$response->toArray()];
            } catch (ServerExceptionInterface|ClientExceptionInterface $e) {
                $lastException = $e;
            }
        }

        throw $lastException;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function downloadUsingSources(HttpSource $source): DownloadedFile
    {
        $lastException = null;
        foreach ($this->getConfiguredClient($source)->concurrent($source) as $response) {
            try {
                $status = $response->getStatusCode();
                $headers = $response->getHeaders();
                $content = $response->getContent();

                $filename = Filesystem\create_temporary_file(prefix: 'extranet-sdk');
                File\write($filename, $content);

                return new DownloadedFile($status, $headers, File\open_read_only($filename));
            } catch (ServerExceptionInterface|ClientExceptionInterface $e) {
                $lastException = $e;
            }
        }

        throw $lastException;
    }

    private function getConfiguredClient(HttpSource $source): HttpClient
    {
        /** @var ?User $user */
        $user = $this->security->getUser();

        if (!$user) {
            return $this->http->withOptions([]);
        }

        $token = \array_key_exists('auth_bearer', $source->options) ? $source->options['auth_bearer'] : $user->token;

        return $this->http->withOptions([
            'auth_bearer' => $source->options['auth_bearer'] ?? $user->token,
        ]);
    }
}
