<?php

declare(strict_types=1);

namespace App\SageParts\P21\Manager;

use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use App\SageParts\P21\DataProvider\CollectionDataProvider;
use App\SageParts\P21\Resources\Supplier;
use Symfony\Component\HttpFoundation\Request;

class SupplierManager
{
    public function __construct(
        private readonly ResourceMetadataCollectionFactoryInterface $resourceMetadataFactory,
        private readonly CollectionDataProvider $collectionProvider,
    ) {
    }

    public function findSupplier(string $supplierNumber): ?Supplier
    {
        $metadata = $this->resourceMetadataFactory->create(Supplier::class);

        $request = new Request(['eq' => ['code' => $supplierNumber]]);

        /** @var Supplier[] $suppliers */
        $suppliers = $this->collectionProvider->provide($metadata->getOperation(forceCollection: true), [], ['request' => $request]);

        if (null === ($suppliers[0] ?? null)) {
            return null;
        }

        return $suppliers[0];
    }
}
