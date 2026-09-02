<?php

declare(strict_types=1);

namespace App\Client;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Token\DataSet;
use Lcobucci\JWT\Token\InvalidTokenStructure;
use Lcobucci\JWT\UnencryptedToken;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

class ApiClient implements HttpClientInterface
{
    public const METHOD_GET = 'GET';
    public const METHOD_PUT = 'PUT';
    public const METHOD_POST = 'POST';

    private const DEFAULT_HEADERS = [
        'Accept' => 'application/ld+json',
        'Content-Type' => 'application/ld+json',
    ];
    private $client;

    private $token;

    public function __construct(string $baseUri)
    {
        $this->client = HttpClient::createForBaseUri($baseUri, [
            'headers' => self::DEFAULT_HEADERS,
        ]);

        if (isset($_COOKIE['_jwt'])) {
            $this->setToken($_COOKIE['_jwt']);
        }
    }

    public function setToken(string $token)
    {
        $this->token = $token;
        // Here we force the content-type to prevent httpclient config to append "application/json" to it, like on intranet, see TTS1723851
        $this->client = $this->client->withOptions([
            'headers' => [
                'Authorization' => 'Bearer '.$this->token,
            ] + self::DEFAULT_HEADERS,
        ]);
    }

    public function isAuthenticated(): bool
    {
        if (null === $this->token) {
            return false;
        }

        try {
            $token = Configuration::forUnsecuredSigner()->parser()->parse($this->token);
        } catch (InvalidTokenStructure $exception) {
            return false;
        }

        // we consider that the authentication is expired if less than an hour is remaining before the expiration
        return $token->isMinimumTimeBefore(new \DateTimeImmutable('+1 hour'));
    }

    public function getUserId(): ?int
    {
        if (null === $this->token) {
            return null;
        }

        /** @var UnencryptedToken $token */
        $token = Configuration::forUnsecuredSigner()->parser()->parse($this->token);

        return $token->claims()->get('id');
    }

    public function getUser(): ?DataSet
    {
        if (null === $this->token) {
            return null;
        }

        /** @var UnencryptedToken $token */
        $token = Configuration::forUnsecuredSigner()->parser()->parse($this->token);

        return $token->claims();
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        return $this->client->request($method, $url, $options);
    }

    public function save(string $entrypoint, array $data, array $options = []): array
    {
        $options = array_merge($options, ['json' => $data]);

        if (isset($data['@id'])) {
            return $this->decodeResponse($this->client->request(self::METHOD_PUT, $data['@id'], $options));
        }

        return $this->decodeResponse($this->client->request(self::METHOD_POST, $entrypoint, $options));
    }

    public function findOneBy(string $url, array $options = []): array
    {
        $results = $this->decodeResponse($this->client->request(self::METHOD_GET, $url, ['query' => $options]));

        if (1 !== \count($results['hydra:member'])) {
            throw new \RangeException(\sprintf('Found %s when 1 was expected.', \count($results['hydra:member'])));
        }

        return $results['hydra:member'][0];
    }

    public function findBy(string $url, array $query = [], $orders = []): array
    {
        if ($orders) {
            if (array_keys($orders) === range(0, \count($orders) - 1)) {
                $orders = array_fill_keys($orders, '');
            }

            foreach ($orders as $field => $direction) {
                $query[\sprintf('order[%s]', $field)] = $direction;
            }
        }
        $options['query'] = $query;

        $results = $this->decodeResponse($this->client->request(self::METHOD_GET, $url, $options));

        return $results['hydra:member'];
    }

    public function find(string $resource, $id = null, array $options = [])
    {
        $entryPoint = null === $id ? $resource : \sprintf('%s/%s', $resource, $id);

        return $this->decodeResponse($this->client->request(self::METHOD_GET, $entryPoint, $options));
    }

    public function get($entryPoint, array $options = [])
    {
        return $this->decodeResponse($this->client->request(self::METHOD_GET, $entryPoint, $options));
    }

    public function stream($responses, ?float $timeout = null): ResponseStreamInterface
    {
        return $this->client->stream($responses, $timeout);
    }

    public static function renderCompositeIdentifier(array $identifiers): string
    {
        $composite = [];
        foreach ($identifiers as $name => $value) {
            $composite[] = \sprintf('%s=%s', $name, $value);
        }

        return implode(';', $composite);
    }

    public function withOptions(array $options): static
    {
        return $this;
    }

    private function decodeResponse(ResponseInterface $response): array
    {
        return json_decode($response->getContent(), true, 512, \JSON_THROW_ON_ERROR);
    }
}
