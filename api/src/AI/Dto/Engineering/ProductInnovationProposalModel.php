<?php

declare(strict_types=1);

namespace App\AI\Dto\Engineering;

use App\AI\Dto\Directory\LocationModel;
use App\AI\Dto\Directory\PeopleModel;

final readonly class ProductInnovationProposalModel
{
    public function __construct(
        public string $shortDescription,
        public string $description,
        public string $process,
        public string $productType,
        public string $model,
        public string $status,
        public \DateTimeInterface $submittedAt,
        public ?\DateTimeInterface $closedAt,
        public ?\DateTimeInterface $suspendedAt,
        public int $suspendedDays,
        public string $resolution,
        public string $rejectionReason,
        public int $importanceFactor,
        public int $finalWeight,
        public ?LocationModel $factory = null,
        public ?PeopleModel $poster = null,
        public ?PeopleModel $initiator = null,
    ) {
    }
}
