<?php

declare(strict_types=1);

namespace LegacyBundle\Doctrine\Mapping\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
class Column
{
    public function __construct(
        public ?string $column = null,
        public ?string $encoding = null,
        public ?string $transformer = null,
        public ?array $options = []
    ) {
    }
}
