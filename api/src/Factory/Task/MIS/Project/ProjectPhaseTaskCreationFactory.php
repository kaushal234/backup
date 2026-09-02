<?php

declare(strict_types=1);

namespace App\Factory\Task\MIS\Project;

use App\Entity\MIS\Project\Project;

readonly class ProjectPhaseTaskCreationFactory extends AbstractProjectTaskCreationFactory
{
    public function createTask(Project $project): array
    {
        $number = $project->getActivePhase()->number;

        return [
            ...$this->constructSharedTask($project),
            'description' => $this->translator->trans(\sprintf('mis_project.task.phase_%d.description', $number), ['%project_id%' => $project->getId(), '%project_name%' => $project->name], 'mis_project'),
            'shortDescription' => $this->translator->trans(\sprintf('mis_project.task.phase_%d.short_description', $number), [], 'mis_project'),
        ];
    }
}
