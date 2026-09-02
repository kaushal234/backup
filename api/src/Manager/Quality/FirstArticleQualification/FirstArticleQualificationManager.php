<?php

declare(strict_types=1);

namespace App\Manager\Quality\FirstArticleQualification;

use App\Entity\Quality\FirstArticleQualification\FirstArticleQualification;
use App\Entity\Quality\FirstArticleQualification\PlanItem;

class FirstArticleQualificationManager
{
    public function getProgressPercentage(FirstArticleQualification $firstArticleQualification): int
    {
        $plan = $firstArticleQualification->getPlan();

        if ($plan->isEmpty()) {
            return 0;
        }

        $totalCompletion = 0;
        foreach ($plan as $planItem) {
            /** @var PlanItem $planItem */
            $totalCompletion += $planItem->getCompletionRate();
        }

        return (int) ($totalCompletion / $plan->count());
    }
}
