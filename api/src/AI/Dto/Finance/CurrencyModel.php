<?php

declare(strict_types=1);

namespace App\AI\Dto\Finance;

final readonly class CurrencyModel
{
    public function __construct(
        public string $name,
    ) {
    }
}
