<?php

declare(strict_types=1);

namespace App\AI\Dto\Service\CustomerServiceRecord;

use App\AI\Dto\Directory\PeopleModel;

final readonly class InterventionModel
{
    /**
     * @param list<PeopleModel> $operators
     */
    public function __construct(
        public string $status,
        public \DateTimeInterface $plannedAt,
        public ?\DateTimeInterface $startedAt,
        public PeopleModel $leader,
        public ?PeopleModel $plannedBy,
        public array $operators,
    ) {
    }
}
