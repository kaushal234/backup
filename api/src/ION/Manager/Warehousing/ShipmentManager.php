<?php

declare(strict_types=1);

namespace App\ION\Manager\Warehousing;

use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use App\ION\DataProvider\CachedIONCollectionDataProvider;
use App\ION\Filter\Warehousing\Shipments\ShipmentOrderFilter;
use App\ION\Resources\Warehousing\Shipments\Shipment;
use Symfony\Component\DependencyInjection\Attribute\Lazy;

readonly class ShipmentManager
{
    public function __construct(
        private ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory,
        #[Lazy] private CachedIONCollectionDataProvider $IONCollectionDataProvider,
    ) {
    }

    public function getShipmentsBySalesOrder(string $salesOrder): array
    {
        $shipmentMetadata = $this->resourceMetadataCollectionFactory->create(Shipment::class);

        return $this->IONCollectionDataProvider->provide($shipmentMetadata->getOperation(forceCollection: true), [], ShipmentOrderFilter::generateContext($salesOrder));
    }
}
