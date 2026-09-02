<?php

declare(strict_types=1);

namespace App\Factory\Task\MIS\Project;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\People;
use App\Entity\MIS\Project\Project;
use App\Repository\Module\ModuleRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract readonly class AbstractProjectTaskCreationFactory
{
    public function __construct(
        protected Security $security,
        protected ModuleRepository $moduleRepository,
        protected TranslatorInterface $translator,
        protected IriConverterInterface $iriConverter
    ) {
    }

    protected function constructSharedTask(Project $project): array
    {
        /** @var People $user */
        $user = $this->security->getUser();

        $activePhase = $project->getActivePhase();
        $dueDate = $activePhase->revisedClosureAt ?? $activePhase->estimatedClosureAt;
        $dueDate = max($dueDate, new \DateTime());

        return [
            'createdBy' => $this->iriConverter->getIriFromResource($user),
            'assignee' => $this->iriConverter->getIriFromResource($project->projectManager),
            'recipients' => [
                $this->iriConverter->getIriFromResource($project->misOwner),
            ],
            'location' => $this->iriConverter->getIriFromResource($project->projectManager->getBusinessUnit()->getLocation()),
            'referenceId' => $project->getId(),
            'module' => $this->iriConverter->getIriFromResource($this->moduleRepository->findOneBy(['name' => 'MIS'])),
            'dueDate' => $dueDate->format('Y-m-d'),
            'startedAt' => (new \DateTime())->format('Y-m-d'),
        ];
    }
}
