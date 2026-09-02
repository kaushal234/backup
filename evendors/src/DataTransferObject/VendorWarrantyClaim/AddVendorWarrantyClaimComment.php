<?php

declare(strict_types=1);

namespace App\DataTransferObject\VendorWarrantyClaim;

use Symfony\Component\Validator\Constraints as Assert;

final class AddVendorWarrantyClaimComment
{
    #[Assert\NotBlank(message: 'purchase_order.comment.add.blank')]
    public string $message;

    #[Assert\File(uploadErrorMessage: 'vendor_warranty_claim.comment.add.file.invalid')]
    public ?string $file = null;
}
