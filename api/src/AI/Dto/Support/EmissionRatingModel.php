<?php

declare(strict_types=1);

namespace App\AI\Dto\Support;

final readonly class EmissionRatingModel
{
    public function __construct(
        public string $name,
    ) {
    }
}
