<?php

declare(strict_types=1);

namespace App\ExternalERP\Mapping\Attribute;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class ERPField
{
    public function __construct(
        public string $name,
    ) {
    }
}
