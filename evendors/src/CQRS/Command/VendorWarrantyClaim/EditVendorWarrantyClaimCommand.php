<?php

declare(strict_types=1);

namespace App\CQRS\Command\VendorWarrantyClaim;

use App\CQRS\Command\CommandInterface;

class EditVendorWarrantyClaimCommand implements CommandInterface
{
    public function __construct(
        public readonly string $iri,
        public readonly float $supplierCreditAmount,
        public readonly string $supplierShippingInstruction,
        public readonly bool $accepted,
        public readonly bool $shipBackDefectivePart,
        public readonly string $supplierReturnMerchandiseAuthorization,
        public readonly ?string $file,
        public readonly ?string $description,
    ) {
    }
}
