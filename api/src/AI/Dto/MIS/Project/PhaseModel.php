<?php

declare(strict_types=1);

namespace App\AI\Dto\MIS\Project;

final readonly class PhaseModel
{
    public function __construct(
        public int $number,
        public ?\DateTimeInterface $estimatedClosureAt,
        public ?\DateTimeInterface $revisedClosureAt,
        public int $estimatedHours,
        public ?int $revisedEstimatedHours,
    ) {
    }
}
