<?php

declare(strict_types=1);

namespace App\Dto\Purchasing\SupplierRanking;

use Symfony\Component\Serializer\Attribute\Groups;

class SupplierRankingStatisticsDto
{
    public function __construct(
        #[Groups(['supplier_ranking_statistics'])]
        public float $totalCompletionRate = 0.0,
        #[Groups(['supplier_ranking_statistics'])]
        public float $mandatoryCompletionRate = 0.0
    ) {
    }
}
