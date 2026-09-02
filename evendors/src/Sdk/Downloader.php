<?php

declare(strict_types=1);

namespace App\Sdk;

use App\Http\Responder;
use App\Sdk\Exception\UnsupportedOperationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

use function sprintf;

final class Downloader
{
    private const FORWARDED_HEADERS = [
        'content-disposition',
        'content-type',
    ];

    public function __construct(
        private readonly ClientInterface $client,
        private readonly Responder $responder,
        private readonly string $projectDir,
    ) {
    }

    /**
     * Return a {@see BinaryFileResponse} for downloading the given `$resource` identified by `$id`.
     *
     * If the resource is not found, a {@see NotFoundHttpException} is thrown.
     *
     * @template T of Resource\ResourceInterface
     *
     * @param class-string<T>                  $resource
     * @param string|array<string, string|int> $identifier
     *
     * @throws NotFoundHttpException
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws UnsupportedOperationException
     */
    public function download(string $resource, string|array $identifier, ?string $filename = null): BinaryFileResponse
    {
        try {
            $file = $this->client->download($resource, $identifier);
        } catch (ClientExceptionInterface $exception) {
            if (Response::HTTP_NOT_FOUND === $exception->getResponse()->getStatusCode()) {
                throw new NotFoundHttpException(previous: $exception);
            }

            throw $exception;
        }
        $response = $this->responder->file($file->handle->getPath());
        $response->deleteFileAfterSend();

        foreach (self::FORWARDED_HEADERS as $header) {
            $values = $file->headers[$header] ?? [];
            $response->headers->set($header, $values);
        }

        if (null !== $filename) {
            $response->headers->set('Content-Disposition', sprintf('inline; filename=%s', $filename));
        }

        return $response;
    }

    /**
     * Return a {@see BinaryFileResponse} for downloading the given `$resource` identified by `$id`.
     *
     * If the resource is not found, a {@see NotFoundHttpException} is thrown.
     *
     * @template T of Resource\ResourceInterface
     *
     * @param class-string<T>      $resource
     * @param array<string, mixed> $criteria
     *
     * @throws NotFoundHttpException
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws UnsupportedOperationException
     */
    public function downloadExcel(string $resource, array $criteria = [], string $filename = 'data'): BinaryFileResponse
    {
        try {
            $file = $this->client->downloadExcel($resource, $criteria);
        } catch (ClientExceptionInterface $exception) {
            if (Response::HTTP_NOT_FOUND === $exception->getResponse()->getStatusCode()) {
                throw new NotFoundHttpException(previous: $exception);
            }

            throw $exception;
        }

        $response = $this->responder->file($file->handle->getPath());
        $response->deleteFileAfterSend();

        foreach (self::FORWARDED_HEADERS as $header) {
            if ('content-disposition' === $header) {
                $response->headers->set('content-disposition', sprintf('inline; filename=%s.xlsx', $filename));
                continue;
            }
            $values = $file->headers[$header] ?? [];
            $response->headers->set($header, $values);
        }

        return $response;
    }

    /**
     * Return a {@see StreamedResponse} for stream the given file for `$resource` identified by `$id` and `$fileId`.
     *
     * If the resource is not found, a default file is return.
     *
     * @template T of Resource\ResourceInterface
     *
     * @param class-string<T>    $resource
     * @param array<string, int> $identifier
     *
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function stream(string $resource, array $identifier): StreamedResponse
    {
        $temporaryFilePath = null;

        try {
            $binaryFileResponse = $this->download($resource, $identifier);

            $file = $binaryFileResponse->getFile();
            $temporaryFilePath = $file->getPathname();
            $disposition = $binaryFileResponse->headers->get('Content-Disposition');
            $type = $binaryFileResponse->headers->get('Content-type');
        } catch (NotFoundHttpException) {
            $file = new File(sprintf('%s/assets/images/no_photo_catalogue.png', $this->projectDir));
            $disposition = HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_INLINE, $file->getFilename());
            $type = $file->getMimeType();
        }

        $content = $file->getContent();

        if (null !== $temporaryFilePath) {
            unlink($temporaryFilePath);
        }

        $response = new StreamedResponse(static function () use ($content): void {
            echo $content;
        });

        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-type', $type);

        return $response;
    }
}
