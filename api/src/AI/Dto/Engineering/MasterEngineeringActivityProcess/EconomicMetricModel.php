<?php

declare(strict_types=1);

namespace App\AI\Dto\Engineering\MasterEngineeringActivityProcess;

final readonly class EconomicMetricModel
{
    public function __construct(
        public ?int $target,
        public ?int $estimateAtCompletion,
        public ?int $actual,
        public ?\DateTimeInterface $actualReportedAt,
    ) {
    }
}
