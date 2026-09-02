<?php

declare(strict_types=1);

namespace App\AI\Dto\Engineering\MasterEngineeringActivityProcess;

final readonly class NonRecurringCostsModel
{
    public function __construct(
        public EconomicMetricModel $developmentHours,
        public EconomicMetricModel $subcontractedEngineeringAmount,
        public EconomicMetricModel $materialAndOtherCosts,
        public EconomicMetricModel $prototypeMaterialCosts,
        public EconomicMetricModel $prototypeLaborHours,
    ) {
    }
}
