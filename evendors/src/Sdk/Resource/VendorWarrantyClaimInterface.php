<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

interface VendorWarrantyClaimInterface extends ResourceInterface
{
    final public const NCR_MODULE = 'NCR';
    final public const WC_MODULE = 'WC';

    public function getModule(): string;
}
