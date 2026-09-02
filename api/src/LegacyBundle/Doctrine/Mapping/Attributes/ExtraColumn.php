<?php

declare(strict_types=1);

namespace LegacyBundle\Doctrine\Mapping\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class ExtraColumn
{
    public function __construct(
        public readonly ?string $column = null,
        public readonly string|int|null $value = null,
        public readonly ?string $transformer = null,
        public readonly ?array $options = [])
    {
    }
}
