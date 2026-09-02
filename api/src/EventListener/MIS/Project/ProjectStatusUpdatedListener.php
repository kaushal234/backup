<?php

declare(strict_types=1);

namespace App\EventListener\MIS\Project;

use App\Entity\MIS\Project\Project;
use App\Request\Task\SubRequestCreateTask;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

readonly class ProjectStatusUpdatedListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    public function __construct(
        private ContainerInterface $serviceLocator,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => [
                ['onStatusUpdated'],
            ],
        ];
    }

    public function onStatusUpdated(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        if (!($project = $request->attributes->get('original_data')) instanceof Project) {
            return;
        }

        $route = $request->attributes->get('_route');

        if (Request::METHOD_POST !== $request->getMethod() || 'mis_project_status' !== $route
            || !\in_array($project->getStatus(), array_merge(Project::PHASES_STATUSES, [Project::PENDING]), true)) {
            return;
        }

        if (Project::PENDING === $project->getStatus()) {
            foreach ($project->getPhases()['0']->getTasks() as $task) {
                $task->setStatus('CLOSED');
                $task->closedAt = new \DateTime();
                $task->comment = 'This task has been close to set the project to PENDING';
                $task->closeComment = $task->comment;
                $this->entityManager->persist($task);
            }
            $this->entityManager->flush();
        } else {
            $this->serviceLocator->get(SubRequestCreateTask::class)->insertTask($project, $event->getResponse());
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return [
            SubRequestCreateTask::class,
        ];
    }
}
