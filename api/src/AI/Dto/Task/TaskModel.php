<?php

declare(strict_types=1);

namespace App\AI\Dto\Task;

use App\AI\Dto\Directory\LocationModel;
use App\AI\Dto\Directory\PeopleModel;
use App\AI\Dto\Module\ModuleModel;

final readonly class TaskModel
{
    /**
     * @param PeopleModel[] $recipients
     */
    public function __construct(
        public string $status,
        public bool $confidential,
        public ?string $indiceFactor,
        public int $referenceId,
        public string $shortDescription,
        public string $description,
        public \DateTimeInterface $createdAt,
        public ?\DateTimeInterface $startedAt,
        public ?\DateTimeInterface $dueDate,
        public ?\DateTimeInterface $rescheduleDate,
        public ?\DateTimeInterface $closedAt,
        public ?\DateTimeInterface $escalationDate,
        public int $escalationTrigger,
        public ?string $escalationTriggerUnit,
        public ?string $lastComment,
        public ?string $closeComment,
        public ?ModuleModel $module,
        public ?PeopleModel $createdBy,
        public ?PeopleModel $assignee,
        public LocationModel $location,
        public array $recipients,
    ) {
    }
}
