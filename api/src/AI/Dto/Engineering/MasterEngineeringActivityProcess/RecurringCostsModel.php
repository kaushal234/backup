<?php

declare(strict_types=1);

namespace App\AI\Dto\Engineering\MasterEngineeringActivityProcess;

final readonly class RecurringCostsModel
{
    public function __construct(
        public EconomicMetricModel $material,
        public EconomicMetricModel $hours,
    ) {
    }
}
