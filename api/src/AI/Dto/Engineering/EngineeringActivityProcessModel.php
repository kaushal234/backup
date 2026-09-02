<?php

declare(strict_types=1);

namespace App\AI\Dto\Engineering;

use App\AI\Dto\Directory\LocationModel;
use App\AI\Dto\Directory\PeopleModel;
use App\AI\Dto\Engineering\MasterEngineeringActivityProcess\MasterEngineeringActivityProcessModel;

final readonly class EngineeringActivityProcessModel
{
    /**
     * @param list<EngineeringActivityProcessPartModel> $parts
     */
    public function __construct(
        public string $shortDescription,
        public string $description,
        public string $status,
        public \DateTimeInterface $openedAt,
        public ?\DateTimeInterface $closedAt,
        public string $category,
        public string $importanceFactor,
        public string $type,
        public string $model,
        public string $actionPlan,
        public string $currency,
        public string $additionalInformation,
        public ?int $expectedHours,
        public ?MasterEngineeringActivityProcessModel $master,
        public ?LocationModel $factory,
        public ?PeopleModel $reportedBy,
        public ?PeopleModel $poster,
        public ?PeopleModel $assignee,
        public array $parts,
    ) {
    }
}
