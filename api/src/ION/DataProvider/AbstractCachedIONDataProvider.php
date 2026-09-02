<?php

declare(strict_types=1);

namespace App\ION\DataProvider;

use ApiPlatform\State\ProviderInterface;
use Symfony\Contracts\Cache\CacheInterface;

abstract class AbstractCachedIONDataProvider implements ProviderInterface
{
    /** @var string */
    final public const CONTEXT_CACHE_KEY = '_ion_cached_provider';

    public function __construct(
        protected CacheInterface $arrayCache
    ) {
    }
}
