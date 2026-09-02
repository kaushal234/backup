<?php

declare(strict_types=1);

namespace App\Sdk\Http;

use Generator;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * An extension of Symfony HTTP Client to support.
 */
interface ClientInterface extends HttpClientInterface
{
    /**
     * Send multiple requests concurrently, and yield responses.
     *
     * @return Generator<int, ResponseInterface, void, void>
     */
    public function concurrent(HttpSource ...$sources): Generator;

    /**
     * Send multiple requests concurrently, and yield responses.
     *
     * @phpstan-return Generator<int, list<ResponseInterface>, void, void>
     *
     * @psalm-return Generator<int, list<ResponseInterface>, void, void>
     */
    public function concurrentDistribution(DistributedHttpSource ...$sources): Generator;
}
