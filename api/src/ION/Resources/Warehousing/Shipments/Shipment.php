<?php

declare(strict_types=1);

namespace App\ION\Resources\Warehousing\Shipments;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Symfony\Action\NotFoundAction;
use App\ION\DataProvider\CachedIONCollectionDataProvider;
use App\ION\Filter\Warehousing\Shipments\ShipmentOrderFilter;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new GetCollection(provider: CachedIONCollectionDataProvider::class),
        new Get(
            requirements: ['id' => '.*'],
            controller: NotFoundAction::class,
            output: false,
            read: false,
        ),
    ],
    routePrefix: 'ion',
    normalizationContext: ['groups' => ['shipment', 'carrier']],
    denormalizationContext: [],
)]
#[ApiFilter(ShipmentOrderFilter::class)]
class Shipment
{
    #[ApiProperty(identifier: true)]
    #[Groups(['shipment'])]
    public string $shipment;

    #[Groups(['shipment'])]
    public string $status;

    #[Groups(['shipment'])]
    public string $procedure;

    #[Groups(['shipment'])]
    public Load $load;

    #[Groups(['shipment'])]
    public ShipFrom $shipFrom;

    #[Groups(['shipment'])]
    public ShipTo $shipTo;

    #[Groups(['shipment'])]
    public Tracking $tracking;

    #[Groups(['shipment'])]
    public References $references;

    #[Groups(['shipment'])]
    public ShippingDocument $shippingDocuments;

    /**
     * @var ShipmentLine[]
     */
    #[Groups(['shipment'])]
    private array $lines = [];

    public function getLines(): array
    {
        return $this->lines;
    }

    public function addLine(ShipmentLine $line): self
    {
        $this->lines[] = $line;
        $line->setShipment($this);

        return $this;
    }

    public function removeLine(ShipmentLine $line): self
    {
        // do nothing, we do not remove element from this resource
        return $this;
    }
}
