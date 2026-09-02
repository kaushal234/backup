<?php

declare(strict_types=1);

namespace App\EventListener\Service\TechnicianOnCall;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Activity\Comment;
use App\Entity\Service\TechnicianOnCall;
use App\Message\Service\TechnicianOnCallJiraCommentSync;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsEventListener(event: KernelEvents::VIEW, method: 'onNewComment', priority: EventPriorities::POST_WRITE)]
class TechnicianOnCallJiraCommentSyncListener extends AbstractTechnicianOnCallListener
{
    public function __construct(
        private readonly ContainerInterface $container,
    ) {
    }

    public static function getSubscribedServices(): array
    {
        return [
            IriConverterInterface::class,
            MessageBusInterface::class,
        ];
    }

    public function onNewComment(ViewEvent $event): void
    {
        $route = $event->getRequest()->attributes->get('_route');

        /** @var Comment $comment */
        $comment = $event->getControllerResult();

        if ('api_comments_post_collection' !== $route || !$comment instanceof Comment) {
            return;
        }

        $iriConverter = $this->container->get(IriConverterInterface::class);
        $technicianOnCall = $iriConverter->getResourceFromIri($comment->getResource());

        if (!$technicianOnCall instanceof TechnicianOnCall || null === $technicianOnCall->jiraTracteasyIssueKey) {
            return;
        }

        $this->container->get(MessageBusInterface::class)->dispatch(
            new TechnicianOnCallJiraCommentSync($iriConverter->getIriFromResource($comment))
        );
    }
}
