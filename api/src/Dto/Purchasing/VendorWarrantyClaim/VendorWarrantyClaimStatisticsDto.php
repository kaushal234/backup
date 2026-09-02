<?php

declare(strict_types=1);

namespace App\Dto\Purchasing\VendorWarrantyClaim;

use Symfony\Component\Serializer\Attribute\Groups;

class VendorWarrantyClaimStatisticsDto
{
    public function __construct(
        #[Groups(['vendor_warranty_claim_statistics'])]
        public float $totalClaimAmount = 0.0,
    ) {
    }
}
