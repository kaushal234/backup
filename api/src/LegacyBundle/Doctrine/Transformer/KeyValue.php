<?php

declare(strict_types=1);

namespace LegacyBundle\Doctrine\Transformer;

class KeyValue
{
    public function __invoke($key, array $options)
    {
        return $options['pairs'][$key] ?? $key;
    }
}
