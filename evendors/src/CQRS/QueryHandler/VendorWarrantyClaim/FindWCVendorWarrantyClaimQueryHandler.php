<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\VendorWarrantyClaim;

use App\CQRS\Query\VendorWarrantyClaim\FindWCVendorWarrantyClaimQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\ClientInterface;
use App\Sdk\Resource\WCVendorWarrantyClaim;

final class FindWCVendorWarrantyClaimQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly ClientInterface $client,
    ) {
    }

    public function __invoke(FindWCVendorWarrantyClaimQuery $query): WCVendorWarrantyClaim
    {
        return $this->client->find(WCVendorWarrantyClaim::class, $query->identifier);
    }
}
