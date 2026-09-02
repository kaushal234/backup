<?php

declare(strict_types=1);

namespace App\AI\Dto\Sales\EquipmentShippingRecord;

use App\AI\Dto\Directory\LocationModel;
use App\AI\Dto\Sales\CustomerModel;

final readonly class EquipmentShippingRecordModel
{
    /**
     * @param EquipmentShippingRecordLineModel[] $lines
     * @param EquipmentShippingRecordCostModel[] $costs
     */
    public function __construct(
        public string $status,
        public string $modality,
        public bool $shipAuthorization,
        public \DateTimeInterface $createdAt,
        public ?string $loadingPlace,
        public ?string $departurePlace,
        public ?string $arrivalPlace,
        public ?string $notes,
        public LocationModel $sso,
        public ?CustomerModel $customer,
        public IncotermModel $incoterm,
        public ?FreightForwarderModel $forwarder,
        public ?FreightForwarderModel $carrier,
        public array $lines,
        public array $costs,
    ) {
    }
}
