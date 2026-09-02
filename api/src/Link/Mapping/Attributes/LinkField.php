<?php

declare(strict_types=1);

namespace App\Link\Mapping\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class LinkField
{
    public function __construct(
        public array $fields = [],
        public ?string $transformer = null,
        public array $options = []
    ) {
    }
}
