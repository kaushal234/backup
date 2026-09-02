<?php

declare(strict_types=1);

namespace App\Http;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AgileGetClient
{
    private const URL_GET_USERS = 'rest/v1/users';

    public function __construct(
        private readonly HttpClientInterface $agileGetClient
    ) {
    }

    public function doRequest($options): array
    {
        $response = $this->agileGetClient->request(Request::METHOD_GET, self::URL_GET_USERS, [
            'query' => $options,
        ]);

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new \Exception(\sprintf('Agile API returned an error: %d %s', $response->getStatusCode(), $response->getContent(false)));
        }

        return json_decode($response->getContent(), true);
    }
}
