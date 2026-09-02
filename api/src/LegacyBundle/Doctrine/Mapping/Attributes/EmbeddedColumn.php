<?php

declare(strict_types=1);

namespace LegacyBundle\Doctrine\Mapping\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
class EmbeddedColumn extends Column
{
    public function __construct(
        public ?string $property = null,
        public ?string $column = null,
        public ?string $encoding = null,
        public ?string $transformer = null,
        public ?array $options = [],
    ) {
        parent::__construct(
            column: $this->column,
            encoding: $this->encoding,
            transformer: $this->transformer,
            options: $this->options,
        );
    }
}
