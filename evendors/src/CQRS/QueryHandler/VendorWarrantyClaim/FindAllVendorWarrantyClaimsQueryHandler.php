<?php

declare(strict_types=1);

namespace App\CQRS\QueryHandler\VendorWarrantyClaim;

use App\CQRS\Query\VendorWarrantyClaim\FindAllVendorWarrantyClaimsQuery;
use App\CQRS\QueryHandler\QueryHandlerInterface;
use App\Sdk\ClientInterface;
use App\Sdk\Resource\VendorWarrantyClaimInterface;
use Psl\Collection\AccessibleCollectionInterface;

final class FindAllVendorWarrantyClaimsQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly ClientInterface $client,
    ) {
    }

    /**
     * @return AccessibleCollectionInterface<int, VendorWarrantyClaimInterface>
     */
    public function __invoke(FindAllVendorWarrantyClaimsQuery $query): AccessibleCollectionInterface
    {
        return $this->client->findAll(VendorWarrantyClaimInterface::class, $query->options);
    }
}
