<?php

declare(strict_types=1);

namespace App\Sdk\Http\ResourceSourceProvider;

use App\Sdk\Http\HttpSource;
use App\Sdk\Resource\Comment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;

class CommentResourceSourceProvider extends AbstractResourceSourceProvider
{
    public function supports(string $resource): bool
    {
        return Comment::class === $resource;
    }

    /**
     * @param string|array<string, string> $identifier
     */
    public function getFindSource(array|string $identifier): ?HttpSource
    {
        return HttpSource::create(
            Request::METHOD_GET,
            \sprintf('%s/%s', $this->getResourceIri(), $identifier['resource_id'])
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function getCreateSource(array $payload): ?HttpSource
    {
        $formFields = [
            'message' => $payload['comment'],
            'resource' => $payload['resource'],
        ];

        if (null !== ($payload['file'] ?? null)) {
            $formFields['file'] = DataPart::fromPath($payload['file']);
        }
        $formData = new FormDataPart($formFields);

        return HttpSource::create(Request::METHOD_POST, '/comments', [
            'headers' => $formData->getPreparedHeaders()->toArray() + ['Accept' => 'application/ld+json'],
            'body' => $formData->bodyToIterable(),
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
        return HttpSource::create(Request::METHOD_GET, $this->getResourceIri(), $criteria);
    }

    public function getPaginationSource(int $page, int $itemsPerPage, array $criteria = []): ?HttpSource
    {
        return HttpSource::create(Request::METHOD_GET, $this->getResourceIri(), [
            'query' => [
                'pagination' => true,
                'page' => $page,
                'itemsPerPage' => $itemsPerPage,
                ...$criteria,
            ],
        ]);
    }

    public function getResourceIri(): string
    {
        return 'comments';
    }
}
