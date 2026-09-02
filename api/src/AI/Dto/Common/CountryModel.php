<?php

declare(strict_types=1);

namespace App\AI\Dto\Common;

final readonly class CountryModel
{
    public function __construct(
        public string $name,
    ) {
    }
}
