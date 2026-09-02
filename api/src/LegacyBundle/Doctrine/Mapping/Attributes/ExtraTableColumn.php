<?php

declare(strict_types=1);

namespace LegacyBundle\Doctrine\Mapping\Attributes;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class ExtraTableColumn
{
    public function __construct(
        public readonly string $column,
        public readonly ?string $property = null,
        public readonly ?string $encoding = null,
        public readonly ?string $transformer = null,
        public readonly ?array $options = [],
        public readonly ?string $value = null,
        public readonly bool $key = false,
    ) {
    }
}
