<?php

declare(strict_types=1);

namespace App\EventListener\MIS\Project;

use App\Entity\MIS\Project\Phase;
use App\Entity\MIS\Project\Project;
use App\Notifier\MIS\Project\ProjectNotifier;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class ProjectWorkflowCompletedListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    public function __construct(
        private ContainerInterface $serviceLocator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.mis_project.completed.to_phase0' => ['completed'],
            'workflow.mis_project.completed.to_phase1' => ['completed'],
            'workflow.mis_project.completed.to_phase2' => ['completed'],
            'workflow.mis_project.completed.to_phase3' => ['completed'],
            'workflow.mis_project.completed.to_phase4' => ['completed'],
            'workflow.mis_project.completed.to_close' => ['completed'],
        ];
    }

    public function completed(CompletedEvent $event)
    {
        $project = $event->getSubject();

        if (!$project instanceof Project) {
            return;
        }

        $phaseToUpdate = null;
        if (Project::CLOSED === $project->getStatus()) {
            /** @var Phase $phaseToUpdate */
            $phaseToUpdate = $project->getPhaseByNumber(4);
        }

        if (null !== $project->getActivePhase() && 0 !== $project->getActivePhase()->number) {
            /** @var Phase $phaseToUpdate */
            $phaseToUpdate = $project->getPhaseByNumber($project->getActivePhase()->number - 1);
        }

        if (null !== $phaseToUpdate) {
            $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);
            $phaseToUpdate->revisedClosureAt = new \DateTime();
            $entityManager->persist($phaseToUpdate);
            $entityManager->flush();
        }

        $this->serviceLocator->get(ProjectNotifier::class)->send($project, 'status', [], ['status' => $project->getStatus()]);
    }

    public static function getSubscribedServices(): array
    {
        return [
            ProjectNotifier::class,
            EntityManagerInterface::class,
        ];
    }
}
