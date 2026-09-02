<?php

declare(strict_types=1);

namespace App\CQRS\Query\VendorWarrantyClaim;

use App\CQRS\Query\QueryInterface;
use App\Sdk\Resource\NCRVendorWarrantyClaim;

/**
 * @implements QueryInterface<NCRVendorWarrantyClaim>
 */
final class FindNCRVendorWarrantyClaimQuery implements QueryInterface
{
    public function __construct(
        public readonly string $identifier,
    ) {
    }
}
