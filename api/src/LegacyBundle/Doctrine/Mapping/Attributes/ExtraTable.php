<?php

declare(strict_types=1);

namespace LegacyBundle\Doctrine\Mapping\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS)]
class ExtraTable
{
    public function __construct(
        public readonly string $table,
        public readonly ?array $properties = [],
    ) {
    }
}
