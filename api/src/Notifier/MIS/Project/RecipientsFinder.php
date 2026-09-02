<?php

declare(strict_types=1);

namespace App\Notifier\MIS\Project;

use App\Entity\MIS\Project\Project;
use App\Repository\Directory\PeopleRepository;

readonly class RecipientsFinder
{
    public function __construct(
        private PeopleRepository $peopleRepository,
    ) {
    }

    public function findTos(Project $project): array
    {
        return array_unique([
            ...$project->getMisMembers(),
            ...$project->getModuleKeyUsers(),
            ...$this->peopleRepository->findGroupMembers('ROLE_CIO'),
            $project->projectManager,
            $project->misOwner,
        ]);
    }
}
