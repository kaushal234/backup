<?php

declare(strict_types=1);

namespace App\CQRS\Command\VendorWarrantyClaim;

use App\CQRS\Command\CommandInterface;
use App\Sdk\Resource\NCRVendorWarrantyClaim;
use App\Sdk\Resource\WCVendorWarrantyClaim;

class AddSupplierCorrectiveActionRequestCommand implements CommandInterface
{
    public function __construct(
        public readonly string $iri,
        public readonly string $issueOrigin,
        public readonly string $correctiveAction,
        public readonly string $description,
        public readonly string $shortDescription,
        public readonly ?string $comment,
        public readonly ?string $file,
        public readonly ?string $fileDescription,
        public readonly WCVendorWarrantyClaim|NCRVendorWarrantyClaim $vendorWarrantyClaim,
    ) {
    }
}
