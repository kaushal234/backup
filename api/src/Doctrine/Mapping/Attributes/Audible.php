<?php

declare(strict_types=1);

namespace App\Doctrine\Mapping\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class Audible
{
    public function __construct(
        public readonly string $type,
    ) {
    }
}
