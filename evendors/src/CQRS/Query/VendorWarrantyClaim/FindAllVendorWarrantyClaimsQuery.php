<?php

declare(strict_types=1);

namespace App\CQRS\Query\VendorWarrantyClaim;

use App\CQRS\Query\QueryInterface;
use App\Sdk\Resource\VendorWarrantyClaimInterface;
use Psl\Collection\AccessibleCollectionInterface;

/**
 * @implements QueryInterface<AccessibleCollectionInterface<VendorWarrantyClaimInterface>>
 */
final class FindAllVendorWarrantyClaimsQuery implements QueryInterface
{
    /**
     * @param array<string> $options
     */
    public function __construct(
        public readonly array $options = [],
    ) {
    }
}
