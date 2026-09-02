<?php

declare(strict_types=1);

namespace App\AI\Dto\Quality\Crab;

final readonly class CrabCodeModel
{
    public function __construct(
        public int $code,
        public string $description,
    ) {
    }
}
