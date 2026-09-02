<?php

declare(strict_types=1);

namespace LegacyBundle\Doctrine\Mapping\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_PROPERTY)]
class Id
{
    public function __construct(
        public readonly ?string $path = null,
    ) {
    }
}
