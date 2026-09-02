<?php

declare(strict_types=1);

namespace App\AI\Dto\Engineering\MasterEngineeringActivityProcess;

final readonly class ScoringModel
{
    public function __construct(
        public int $importanceFactor,
        public int $finalWeight,
    ) {
    }
}
