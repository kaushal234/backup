<?php

declare(strict_types=1);

namespace App\Sdk\Http;

/**
 * An HTTPSource represents an HTTP request that needs to be performed in order
 * to retrieve a specific resource.
 */
final class HttpSource
{
    /**
     * @param non-empty-string     $method
     * @param non-empty-string     $uri
     * @param array<string, mixed> $options
     */
    private function __construct(
        public readonly string $method,
        public readonly string $uri,
        public readonly array $options = [],
    ) {
    }

    /**
     * @param non-empty-string     $method
     * @param non-empty-string     $uri
     * @param array<string, mixed> $options
     */
    public static function create(string $method, string $uri, array $options = []): self
    {
        return new self($method, $uri, $options);
    }
}
