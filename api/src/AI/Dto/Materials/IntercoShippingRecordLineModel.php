<?php

declare(strict_types=1);

namespace App\AI\Dto\Materials;

final readonly class IntercoShippingRecordLineModel
{
    public function __construct(
        public string $packingSlipNumber,
    ) {
    }
}
