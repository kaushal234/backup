<?php

declare(strict_types=1);

namespace App\AI\Dto\Quality;

use App\AI\Dto\Directory\PeopleModel;

final readonly class DerogationModel
{
    public function __construct(
        public string $status,
        public string $shortDescription,
        public string $description,
        public \DateTimeInterface $dueDate,
        public ?\DateTimeInterface $closedAt,
        public PeopleModel $assignor,
        public ?PeopleModel $assignee,
        public ?PeopleModel $closedBy,
    ) {
    }
}
