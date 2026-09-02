<?php

declare(strict_types=1);

namespace App\AI\Dto\Engineering\MasterEngineeringActivityProcess;

final readonly class EconomicsHeaderModel
{
    public function __construct(
        public bool $isEngineeringProgramCapitalized,
        public string $programCurrency,
        public string $costCalculationMethod,
    ) {
    }
}
