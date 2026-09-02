<?php

declare(strict_types=1);

namespace App\AI\Dto\MIS\TroubleTicket;

use App\AI\Dto\Directory\PeopleModel;
use App\AI\Dto\Module\ModuleModel;

final readonly class TroubleTicketModel
{
    /**
     * @param list<PeopleModel> $ccs
     * @param list<PeopleModel> $additionalOwners
     */
    public function __construct(
        public string $status,
        public string $shortDescription,
        public string $description,
        public ?string $indiceFactor,
        public ?string $jiraIssueNumber,
        public ?string $url,
        public ?string $referer,
        public ?string $hostName,
        public ?string $satisfaction,
        public bool $isAddToUserStories,
        public \DateTimeInterface $createdAt,
        public ?\DateTimeInterface $dueDate,
        public ?\DateTimeInterface $closedAt,
        public ?\DateTimeInterface $solutionProposedAt,
        public ?\DateTimeInterface $lastCommentedAt,
        public ?ModuleModel $module,
        public ?TypeModel $type,
        public ?PeopleModel $createdBy,
        public ?PeopleModel $assignee,
        public ?PeopleModel $misAssignee,
        public array $ccs,
        public array $additionalOwners,
    ) {
    }
}
