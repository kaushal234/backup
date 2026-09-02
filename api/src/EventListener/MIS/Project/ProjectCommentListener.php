<?php

declare(strict_types=1);

namespace App\EventListener\MIS\Project;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\MIS\Project\Project;
use App\Request\Activity\CommentRequestManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class ProjectCommentListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    public function __construct(
        private readonly ContainerInterface $serviceLocator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['onPostStatusUpdate', EventPriorities::POST_WRITE],
            ],
        ];
    }

    public function onPostStatusUpdate(ViewEvent $event): void
    {
        $project = $event->getControllerResult();
        $request = $event->getRequest();
        $route = $request->attributes->get('_route');

        if (!$project instanceof Project || (Request::METHOD_POST === $request->getMethod() && !\in_array($route, ['mis_project_status'], true))) {
            return;
        }

        /** @var Project $previous */
        $previous = $request->attributes->get('previous_data');

        if (null !== $project->comment) {
            $file = $request->files->get('file');
            $project->lastComment = $project->comment;
            $comment = \sprintf('Project status changed from %s to %s.
            %s', $previous->getStatus(), $project->getStatus(), $project->comment);
            $this->serviceLocator->get(CommentRequestManager::class)->insertComment($project, $comment, $file);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return [
            CommentRequestManager::class,
        ];
    }
}
