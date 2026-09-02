<?php

declare(strict_types=1);

namespace AppBundle\Manager\Purchasing\VendorWarrantyClaim;

use ApiBundle\Model\ApiData;
use AppBundle\Controller\Purchasing\VendorWarrantyClaimController;

class IriResolver
{
    public function resolve(ApiData|array $vendorWarrantyClaim): string
    {
        $type = $vendorWarrantyClaim['@type'] ?? null;

        return match ($type) {
            'NcrVendorWarrantyClaim' => VendorWarrantyClaimController::NCR_VENDOR_WARRANTY_CLAIM_URL,
            'WcVendorWarrantyClaim' => VendorWarrantyClaimController::WC_VENDOR_WARRANTY_CLAIM_URL,
            default => throw new \InvalidArgumentException(\sprintf('Unsupported VendorWarrantyClaim type "%s"', $vendorWarrantyClaim['@type'] ?? 'undefined')),
        };
    }
}
