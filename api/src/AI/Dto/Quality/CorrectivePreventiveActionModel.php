<?php

declare(strict_types=1);

namespace App\AI\Dto\Quality;

use App\AI\Dto\Directory\LocationModel;
use App\AI\Dto\Directory\PeopleModel;

final readonly class CorrectivePreventiveActionModel
{
    public function __construct(
        public string $shortDescription,
        public string $description,
        public string $department,
        public string $type,
        public string $status,
        public ?string $lastStatus,
        public ?\DateTimeInterface $openedDate,
        public ?\DateTimeInterface $targetDate,
        public ?\DateTimeInterface $closedDate,
        public ?\DateTimeInterface $suspendedDate,
        public int $daysSuspended,
        public string $containmentAction,
        public string $rootCause,
        public string $correctiveAction,
        public string $preventiveAction,
        public string $resolution,
        public string $rejectionReason,
        public int $importanceFactor,
        public int $finalWeight,
        public ?string $verificationDescription,
        public ?LocationModel $location,
        public ?PeopleModel $projectLeader,
        public ?PeopleModel $poster,
        public ?PeopleModel $initiator,
    ) {
    }
}
