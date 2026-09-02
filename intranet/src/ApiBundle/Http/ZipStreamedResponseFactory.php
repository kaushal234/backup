<?php

declare(strict_types=1);

namespace ApiBundle\Http;

use ApiBundle\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ZipStreamedResponseFactory
{
    private readonly Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $requestUrl
     *
     * @return StreamedResponse
     */
    public function create($requestUrl, $filename)
    {
        $filename = str_replace([',', ';'], '', $filename);
        $zip = $this->client->request($requestUrl, null, null, Request::METHOD_GET, [
            'headers' => [
                'Content-Type' => 'application/zip',
                'Accept' => 'application/zip',
            ],
        ]);

        // Fire exception to catch error before stream response
        $zip->getHeaders();

        $response = new StreamedResponse(static function () use ($zip) {
            echo $zip->getContent();
        });

        $response->headers->set('Content-Disposition', \sprintf('inline; filename=%s.zip', $filename));
        $response->headers->set('Content-type', 'application/zip');

        return $response;
    }
}
