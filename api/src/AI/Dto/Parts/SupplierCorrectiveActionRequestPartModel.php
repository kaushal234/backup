<?php

declare(strict_types=1);

namespace App\AI\Dto\Parts;

final readonly class SupplierCorrectiveActionRequestPartModel
{
    public function __construct(
        public \DateTimeInterface $createdAt,
        public string $partNumber,
        public string $description,
        public float $quantity,
        public ?string $unitOfMeasure,
    ) {
    }
}
