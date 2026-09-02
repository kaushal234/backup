<?php

declare(strict_types=1);

namespace App\Doctrine\Mapping\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class LoggedName
{
    public function __construct(
        public readonly string $name,
    ) {
    }
}
