<?php

declare(strict_types=1);

namespace App\DataTransferObject\VendorWarrantyClaim;

use Symfony\Component\Validator\Constraints as Assert;

class AddSupplierCorrectiveActionRequest
{
    #[Assert\NotBlank(message: 'vendor_warranty_claim.scar.blank.issue_origin')]
    public string $issueOrigin;

    #[Assert\NotBlank(message: 'vendor_warranty_claim.scar.blank.corrective_action')]
    public string $correctiveAction;

    #[Assert\NotBlank(message: 'vendor_warranty_claim.scar.blank.description')]
    public string $description;

    #[Assert\NotBlank(message: 'vendor_warranty_claim.scar.blank.short_description')]
    public string $shortDescription;

    public ?string $comment = null;

    public ?string $file = null;

    public ?string $fileDescription = null;
}
