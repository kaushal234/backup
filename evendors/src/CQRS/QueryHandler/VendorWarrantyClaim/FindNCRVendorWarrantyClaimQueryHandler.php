<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\VendorWarrantyClaim;

use App\CQRS\Query\VendorWarrantyClaim\FindNCRVendorWarrantyClaimQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\ClientInterface;
use App\Sdk\Resource\NCRVendorWarrantyClaim;

final class FindNCRVendorWarrantyClaimQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly ClientInterface $client,
    ) {
    }

    public function __invoke(FindNCRVendorWarrantyClaimQuery $query): NCRVendorWarrantyClaim
    {
        return $this->client->find(NCRVendorWarrantyClaim::class, $query->identifier);
    }
}
