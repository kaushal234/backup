<?php

declare(strict_types=1);

namespace ApiBundle\Http;

use ApiBundle\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileStreamedResponseFactory
{
    public function __construct(
        private readonly Client $client,
    ) {
    }

    /**
     * @param string $requestUrl
     */
    public function create($requestUrl, array $options = [], $filename = null, string $contentDisposition = ResponseHeaderBag::DISPOSITION_INLINE, bool $forceDownload = false): StreamedResponse
    {
        $file = $this->client->request($requestUrl, null, null, Request::METHOD_GET, $options);

        // Fire exception to catch error before stream response
        $headers = $file->getHeaders();

        $response = new StreamedResponse(static function () use ($file) {
            echo $file->getContent();
        });

        if ($forceDownload) {
            $contentDisposition = ResponseHeaderBag::DISPOSITION_ATTACHMENT;
        }

        if (null !== $filename) {
            $response->headers->set('Content-Disposition', \sprintf('%s; filename=%s', $contentDisposition, $filename));
        } else {
            $headerContentDisposition = \is_array($headers['content-disposition']) ? $headers['content-disposition'][0] : $headers['content-disposition'];
            $contentDispositionType = str_replace('inline;', \sprintf('%s;', $contentDisposition), $headerContentDisposition);

            $response->headers->set('Content-Disposition', $contentDispositionType);
        }

        if (isset($headers['content-type'])) {
            $response->headers->set('Content-type', $headers['content-type']);
        }

        return $response;
    }
}
