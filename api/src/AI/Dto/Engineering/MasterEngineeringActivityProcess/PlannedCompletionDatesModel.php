<?php

declare(strict_types=1);

namespace App\AI\Dto\Engineering\MasterEngineeringActivityProcess;

final readonly class PlannedCompletionDatesModel
{
    public function __construct(
        public ?\DateTimeInterface $milestone0,
        public ?\DateTimeInterface $milestone1,
        public ?\DateTimeInterface $milestone2,
        public ?\DateTimeInterface $milestone3,
        public ?\DateTimeInterface $milestone4,
    ) {
    }
}
