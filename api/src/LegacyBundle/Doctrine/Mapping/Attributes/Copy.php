<?php

declare(strict_types=1);

namespace LegacyBundle\Doctrine\Mapping\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
class Copy
{
    public function __construct(
        public readonly ?string $table = null,
        public readonly array $columns = [],
        public readonly ?array $options = [])
    {
    }
}
