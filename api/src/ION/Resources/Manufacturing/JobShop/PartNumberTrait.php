<?php

declare(strict_types=1);

namespace App\ION\Resources\Manufacturing\JobShop;

use App\ION\Resources\Manufacturing\ItemRevisionStatus;
use Symfony\Component\Serializer\Attribute\Groups;

/**
 * deprecated.
 */
trait PartNumberTrait
{
    #[Groups(['cbom'])]
    public ?string $unitOfMeasure = null;

    #[Groups(['cbom'])]
    public ?string $itemSignalCode = null;

    #[Groups(['cbom'])]
    public ?string $itemDescription = null;

    #[Groups(['cbom'])]
    public ?string $itemOtherDescription = null;

    #[Groups(['cbom'])]
    public ?string $itemSelectionCode = null;

    #[Groups(['cbom'])]
    public ?string $itemType = null;

    #[Groups(['cbom'])]
    public ?string $itemGroup = null;

    #[Groups(['cbom'])]
    public bool $customized = false;

    #[Groups(['cbom'])]
    public ?string $extraInformation = null;

    #[Groups(['cbom'])]
    public ?string $purchaseStatisticsGroup = null;

    #[Groups(['cbom'])]
    public ?string $buyFromBusinessPartner = null;

    #[Groups(['cbom'])]
    public ?string $buyFromBusinessPartnerName = null;

    #[Groups(['cbom'])]
    public ?string $buyer = null;

    #[Groups(['cbom'])]
    public ?int $supplyTime = null;

    #[Groups(['cbom'])]
    public ?string $engineeringRevision = null;

    #[Groups(['cbom'])]
    public ?string $engineeringSignalCode = null;

    #[Groups(['cbom'])]
    public ?string $engineeringDescription = null;

    #[Groups(['cbom'])]
    public ?string $engineeringOtherDescription = null;

    #[Groups(['cbom'])]
    public ?string $engineeringSelectionCode = null;

    #[Groups(['cbom'])]
    public ?int $orderQuantityIncrement = null;

    #[Groups(['cbom'])]
    public ?int $minimumOrderQuantity = null;

    #[Groups(['cbom'])]
    public ?int $safetyStock = null;

    #[Groups(['cbom'])]
    public ?string $warehouse = null;

    #[Groups(['cbom'])]
    public ?string $salesPriceGroup = null;

    #[Groups(['cbom'])]
    public ?float $estimatedStandardCost = null;

    #[Groups(['cbom'])]
    public ?bool $backflushIfMaterial = null;

    #[Groups(['cbom'])]
    public ?bool $phantom = null;

    #[Groups(['cbom'])]
    public ?string $signalCodeDescription = null;

    #[Groups(['cbom'])]
    public bool $preventive;

    #[Groups(['cbom'])]
    public bool $maintenance;

    #[Groups(['cbom'])]
    public bool $overhaul;

    #[Groups(['cbom'])]
    public bool $critical;

    #[Groups(['cbom'])]
    public ?string $engineeringRevisionEffectiveDate = null;

    #[Groups(['cbom'])]
    public ?string $engineeringRevisionExpiryDate = null;

    #[Groups(['cbom'])]
    public ?string $engineeringRevisionDescription = null;

    #[Groups(['cbom'])]
    public ?string $engineeringRevisionDrawing = null;

    #[Groups(['cbom'])]
    public function isExpired(): bool
    {
        return ItemRevisionStatus::expired($this->engineeringRevisionExpiryDate);
    }
}
