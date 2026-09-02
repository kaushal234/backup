<?php

declare(strict_types=1);

namespace App\Sdk\Http;

use Psl\Vec;

/**
 * A DistributedHttpSource represents a set of HTTP requests that need to be performed in order to retrieve a collection of resources.
 */
final class DistributedHttpSource
{
    /**
     * @param non-empty-list<HttpSource> $sources
     */
    private function __construct(
        public readonly array $sources,
    ) {
    }

    public static function combine(HttpSource ...$sources): self
    {
        /** @var non-empty-list<HttpSource> $sources */
        $sources = Vec\values($sources);

        return new self($sources);
    }
}
