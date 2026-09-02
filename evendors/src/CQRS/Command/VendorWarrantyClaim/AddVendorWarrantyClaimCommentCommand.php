<?php

declare(strict_types=1);

namespace App\CQRS\Command\VendorWarrantyClaim;

use App\CQRS\Command\CommandInterface;

final class AddVendorWarrantyClaimCommentCommand implements CommandInterface
{
    public function __construct(
        public readonly string $iri,
        public readonly string $message,
        public readonly ?string $file,
    ) {
    }
}
