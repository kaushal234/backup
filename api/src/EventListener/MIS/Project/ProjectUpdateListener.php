<?php

declare(strict_types=1);

namespace App\EventListener\MIS\Project;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\MIS\Project\Project;
use App\Factory\EmailChangeSetFactory;
use App\Notifier\MIS\Project\ProjectNotifier;
use App\Request\Activity\CommentRequestManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class ProjectUpdateListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    public function __construct(
        private readonly ContainerInterface $serviceLocator,
        private array $changeSet = [],
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['onUpdate', EventPriorities::POST_WRITE],
                ['onComment', EventPriorities::POST_WRITE],
                ['onPreUpdate', EventPriorities::PRE_WRITE],
            ],
        ];
    }

    public function onUpdate(ViewEvent $event): void
    {
        $request = $event->getRequest();
        $project = $event->getControllerResult();
        if (!$project instanceof Project || Request::METHOD_PUT !== $request->getMethod()) {
            return;
        }

        $this->serviceLocator->get(ProjectNotifier::class)->send($project, 'update', $this->changeSet);
    }

    public function onComment(ViewEvent $event): void
    {
        $request = $event->getRequest();
        $project = $event->getControllerResult();
        /** @var Operation $operation */
        $operation = $request->attributes->get('_api_operation');
        if (!$project instanceof Project || !\in_array($operation->getName(), ['mis_project_close', 'mis_project_comment', 'mis_update_status'], true)) {
            return;
        }

        if (null === $project->comment) {
            return;
        }

        $file = $request->files->get('file');
        $project->lastComment = $project->comment;
        $this->serviceLocator->get(CommentRequestManager::class)->insertComment($project, $project->comment, $file);

        if ('mis_project_comment' === $operation->getName()) {
            $this->serviceLocator->get(ProjectNotifier::class)->send($project, 'comment', []);
        }
    }

    public function onPreUpdate(ViewEvent $event): void
    {
        $project = $event->getControllerResult();
        $request = $event->getRequest();

        if (!$project instanceof Project || !$request->isMethod(Request::METHOD_PUT)) {
            return;
        }

        $this->changeSet = ['changeSet' => $this->serviceLocator->get(EmailChangeSetFactory::class)->createChangeSetForEmail($project, [], 'Y-m-d')];
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return [
            EmailChangeSetFactory::class,
            ProjectNotifier::class,
            CommentRequestManager::class,
        ];
    }
}
