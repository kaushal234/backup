<?php

declare(strict_types=1);

namespace App\Factory\Task\MIS\Project;

use App\Entity\MIS\Project\Project;

readonly class ProjectPrivacyTaskCreationFactory extends AbstractProjectTaskCreationFactory
{
    public function createTask(Project $project): array
    {
        return [
            ...$this->constructSharedTask($project),
            'description' => $this->translator->trans('mis_project.task.privacy_design.description', ['%project_id%' => $project->getId(), '%project_name%' => $project->name], 'mis_project'),
            'shortDescription' => $this->translator->trans('mis_project.task.privacy_design.short_description', [], 'mis_project'),
        ];
    }
}
