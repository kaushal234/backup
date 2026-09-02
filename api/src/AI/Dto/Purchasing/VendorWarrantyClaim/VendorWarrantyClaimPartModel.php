<?php

declare(strict_types=1);

namespace App\AI\Dto\Purchasing\VendorWarrantyClaim;

final readonly class VendorWarrantyClaimPartModel
{
    public function __construct(
        public string $partNumber,
        public string $description,
        public float $quantity,
        public ?string $unitOfMeasure,
        public ?float $standardCost,
        public ?string $serialNumber,
        public ?string $vendorPartNumber,
        public ?string $vendorSerialNumber,
        public ?string $failureType,
        public ?string $failureSystem,
        public bool $ship,
        public ?int $receivedQuantity,
    ) {
    }
}
