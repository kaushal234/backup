<?php

declare(strict_types=1);

namespace App\AI\Dto\Support;

use App\AI\Dto\Directory\LocationModel;
use App\AI\Dto\Directory\PeopleModel;

final readonly class ProductDemeritClaimModel
{
    public function __construct(
        public ?LocationModel $factory,
        public string $productType,
        public string $productModel,
        public string $lastStatus,
        public string $status,
        public ?\DateTimeInterface $openingDate,
        public ?\DateTimeInterface $closingDate,
        public ?\DateTimeInterface $suspensionDate,
        public int $daysSuspended,
        public string $shortDescription,
        public string $description,
        public string $containmentAction,
        public string $rootCause,
        public string $correctiveAction,
        public string $preventiveAction,
        public string $resolution,
        public string $rejectionReason,
        public int $importanceFactor,
        public int $finalFocusWeight,
        public ?PeopleModel $poster,
        public ?PeopleModel $initiator,
        public ?PeopleModel $assignee,
        public ?string $verificationDescription,
        public ?\DateTimeInterface $statusUpdatedAt,
        public bool $involvesIbs,
        public bool $involvesIhs,
        public bool $involvesLink,
        public bool $readyToClose,
    ) {
    }
}
