<?php

declare(strict_types=1);

namespace App\AI\Dto\Materials;

use App\AI\Dto\Directory\LocationModel;
use App\AI\Dto\Directory\PeopleModel;

final readonly class IntercoShippingRecordModel
{
    /**
     * @param list<IntercoShippingRecordLineModel> $lines
     */
    public function __construct(
        public \DateTimeInterface $openedAt,
        public string $status,
        public string $erpCustomerNumber,
        public string $transportationType,
        public string $containerNumber,
        public ?string $containerType,
        public string $trackingNumber,
        public ?\DateTimeInterface $outboundDate,
        public ?\DateTimeInterface $shippingDate,
        public ?\DateTimeInterface $etaDate,
        public string $notes,
        public ?PeopleModel $poster,
        public ?LocationModel $fromBusinessUnit,
        public ?LocationModel $toBusinessUnit,
        public array $lines,
    ) {
    }
}
