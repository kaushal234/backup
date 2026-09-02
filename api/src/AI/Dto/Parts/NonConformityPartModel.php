<?php

declare(strict_types=1);

namespace App\AI\Dto\Parts;

final readonly class NonConformityPartModel
{
    public function __construct(
        public ?string $reference,
        public ?string $referenceNumber,
        public ?string $serialNumber,
        public ?float $standardCost,
    ) {
    }
}
