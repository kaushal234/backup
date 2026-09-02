<?php

declare(strict_types=1);

namespace App\Dto\Support;

readonly class TopLateSolDto
{
    public function __construct(
        public string $sol,
        public ?string $buyer,
        public ?string $endUser,
        public ?string $promiseDate,
        public ?string $estimatedGreenTagDate,
        public ?string $product,
        public int $quantity,
    ) {
    }
}
