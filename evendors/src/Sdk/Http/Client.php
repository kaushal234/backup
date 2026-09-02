<?php

declare(strict_types=1);

namespace App\Sdk\Http;

use Generator;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

final class Client implements ClientInterface
{
    public function __construct(
        private readonly HttpClientInterface $internalApiClient,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function concurrent(HttpSource ...$sources): Generator
    {
        $responses = [];
        foreach ($sources as $index => $source) {
            $response = $this->request($source->method, $source->uri, $source->options);
            $responses[$index] = $response;
        }

        foreach ($this->stream($responses) as $response => $chunk) {
            if ($chunk->isLast()) {
                foreach ($responses as $index => $reference) {
                    if ($reference === $response) {
                        yield $index => $response;
                    }
                }
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function concurrentDistribution(DistributedHttpSource ...$sources): Generator
    {
        $pending = [];
        $responseMap = [];
        $responses = [];
        foreach ($sources as $index => $distribution) {
            foreach ($distribution->sources as $source) {
                $response = $this->request($source->method, $source->uri, $source->options);
                $pending[$index] = ($pending[$index] ?? 0) + 1;
                $responseMap[$index][] = $response;
                $responses[] = $response;
            }
        }

        foreach ($this->stream($responses) as $response => $chunk) {
            if ($chunk->isLast()) {
                foreach ($responseMap as $index => $references) {
                    foreach ($references as $reference) {
                        if ($reference === $response) {
                            --$pending[$index];

                            if (0 === $pending[$index]) {
                                unset($pending[$index], $responseMap[$index]);

                                yield $index => $references;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * {@inheritDoc}
     *
     * @param array<string, mixed> $options
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
        return new self($this->internalApiClient->withOptions($options));
    }
}
