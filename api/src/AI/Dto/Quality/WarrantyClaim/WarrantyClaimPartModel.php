<?php

declare(strict_types=1);

namespace App\AI\Dto\Quality\WarrantyClaim;

final readonly class WarrantyClaimPartModel
{
    public function __construct(
        public string $supplyIt,
        public string $partNumber,
        public string $partDescription,
        public string $brand,
        public string $quantity,
        public ?string $quantityReturned,
        public ?\DateTimeInterface $returnedDate,
        public string $unitOfMeasure,
        public string $failureType,
        public string $failureSystem,
        public string $replacementSerialNumber,
        public ?string $notes,
    ) {
    }
}
