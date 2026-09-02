<?php

declare(strict_types=1);

namespace App\Sdk\Http;

use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

class Client implements HttpClientInterface
{
    final public function __construct(
        private readonly HttpClientInterface $internalApiClient,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function concurrent(HttpSource $source): \Generator
    {
        $response = $this->request($source->method, $source->uri, $source->options);

        foreach ($this->stream($response) as $response => $chunk) {
            if ($chunk->isLast()) {
                yield $response;
            }
        }
    }

    /**
     * @param array<string, mixed> $options
     *
     * @throws TransportExceptionInterface
     */
    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        return $this->internalApiClient->request($method, $url, $options);
    }

    /**
     * {@inheritDoc}
     */
    public function stream(iterable|ResponseInterface $responses, ?float $timeout = null): ResponseStreamInterface
    {
        return $this->internalApiClient->stream($responses, $timeout);
    }

    /**
     * {@inheritDoc}
     *
     * @param array<string, mixed> $options
     */
    public function withOptions(array $options): static
    {
        return new static($this->internalApiClient->withOptions($options));
    }
}
