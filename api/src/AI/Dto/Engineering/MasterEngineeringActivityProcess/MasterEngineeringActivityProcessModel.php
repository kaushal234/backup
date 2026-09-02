<?php

declare(strict_types=1);

namespace App\AI\Dto\Engineering\MasterEngineeringActivityProcess;

use App\AI\Dto\Directory\LocationModel;
use App\AI\Dto\Directory\PeopleModel;

final readonly class MasterEngineeringActivityProcessModel
{
    public function __construct(
        public string $productType,
        public string $model,
        public string $status,
        public bool $isPrivate,
        public ?string $type,
        public string $purpose,
        public string $shortDescription,
        public string $description,
        public string $resolution,
        public string $rejectionReason,
        public ?LocationModel $factory,
        public ?PeopleModel $poster,
        public ?PeopleModel $projectLeader,
        public EconomicsHeaderModel $economicsHeader,
        public NonRecurringCostsModel $nonRecurringCosts,
        public RecurringCostsModel $recurringCosts,
        public PlannedCompletionDatesModel $plannedCompletionDates,
        public LifecycleDatesModel $lifecycleDates,
        public ScoringModel $scoring,
    ) {
    }
}
