<?php

declare(strict_types=1);

namespace App\AI\Dto\Engineering;

final readonly class EngineeringActivityProcessPartModel
{
    public function __construct(
        public string $partNumber,
    ) {
    }
}
