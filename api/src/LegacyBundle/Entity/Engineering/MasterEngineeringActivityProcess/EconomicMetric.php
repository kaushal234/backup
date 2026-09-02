<?php

declare(strict_types=1);

namespace LegacyBundle\Entity\Engineering\MasterEngineeringActivityProcess;

final readonly class EconomicMetric
{
    public function __construct(
        public ?int $target,
        public ?int $estimateAtCompletion,
        public ?int $actual,
        public ?\DateTimeInterface $actualReportedAt,
    ) {
    }
}
