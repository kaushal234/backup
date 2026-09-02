<?php

declare(strict_types=1);

namespace App\AI\Dto\Sales\MarketIntelligence;

final readonly class MarketIntelligenceTypeModel
{
    public function __construct(
        public string $name,
    ) {
    }
}
