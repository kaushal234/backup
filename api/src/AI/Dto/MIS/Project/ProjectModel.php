<?php

declare(strict_types=1);

namespace App\AI\Dto\MIS\Project;

use App\AI\Dto\Directory\PeopleModel;
use App\AI\Dto\Module\ModuleModel;

final readonly class ProjectModel
{
    /**
     * @param list<PeopleModel> $moduleKeyUsers
     * @param list<PeopleModel> $misMembers
     * @param list<PhaseModel>  $phases
     * @param list<string>      $tags
     */
    public function __construct(
        public string $name,
        public string $description,
        public string $indicesFactor,
        public string $status,
        public bool $confidential,
        public \DateTimeInterface $createdAt,
        public \DateTimeInterface $startedAt,
        public ?\DateTimeInterface $lastCommentedAt,
        public ?string $lastComment,
        public ?string $conclusion,
        public ?string $teamsLink,
        public ?string $region,
        public ?ModuleModel $module,
        public ?PeopleModel $projectManager,
        public ?PeopleModel $misOwner,
        public array $moduleKeyUsers,
        public array $misMembers,
        public array $phases,
        public array $tags,
    ) {
    }
}
