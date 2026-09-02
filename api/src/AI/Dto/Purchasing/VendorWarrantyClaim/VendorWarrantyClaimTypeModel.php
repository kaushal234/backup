<?php

declare(strict_types=1);

namespace App\AI\Dto\Purchasing\VendorWarrantyClaim;

final readonly class VendorWarrantyClaimTypeModel
{
    public function __construct(
        public string $name,
        public string $description,
    ) {
    }
}
