<?php

declare(strict_types=1);

namespace App\AI\Dto\Engineering\MasterEngineeringActivityProcess;

final readonly class LifecycleDatesModel
{
    public function __construct(
        public ?\DateTimeInterface $createdOn,
        public ?\DateTimeInterface $closedOn,
        public ?\DateTimeInterface $suspendedOn,
        public int $suspendedDaysCount,
    ) {
    }
}
