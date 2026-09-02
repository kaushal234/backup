<?php

declare(strict_types=1);

namespace App\DataTransferObject\VendorWarrantyClaim;

use Symfony\Component\Validator\Constraints as Assert;

class EditVendorWarrantyClaim
{
    #[Assert\NotBlank(message: 'vendor_warranty_claim.edit.blank.supplier_credit_amount')]
    public float $supplierCreditAmount;

    #[Assert\NotBlank(message: 'vendor_warranty_claim.edit.blank.supplier_credit_amount')]
    public string $supplierShippingInstruction;

    public bool $accepted;

    public bool $shipBackDefectivePart;

    #[Assert\NotBlank(message: 'vendor_warranty_claim.edit.blank.supplier_return_merchandise_authorization')]
    public string $supplierReturnMerchandiseAuthorization;

    #[Assert\File(uploadErrorMessage: 'vendor_warranty_claim.comment.add.file.invalid')]
    public ?string $file = null;

    public ?string $description = null;
}
