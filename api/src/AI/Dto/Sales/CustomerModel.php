<?php

declare(strict_types=1);

namespace App\AI\Dto\Sales;

final readonly class CustomerModel
{
    public function __construct(
        public string $name,
        public string $status,
    ) {
    }
}
