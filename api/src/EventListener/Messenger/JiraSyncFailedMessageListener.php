<?php

declare(strict_types=1);

namespace App\EventListener\Messenger;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use App\Entity\Activity\Comment;
use App\Entity\Service\TechnicianOnCall;
use App\Entity\User;
use App\Factory\Service\TechnicianOnCallJiraCommentSyncFailureCommentFactory;
use App\Factory\Service\TechnicianOnCallTracteasyHelpdeskIssueCreationCommentFactory;
use App\Jira\DataProvider\JiraItemDataProvider;
use App\Jira\Resources\TracteasyIssueType;
use App\Message\Service\TechnicianOnCallJiraCommentSync;
use App\Message\Service\TechnicianOnCallJiraTracteasyIssueCreate;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;

#[AsEventListener]
class JiraSyncFailedMessageListener
{
    public function __construct(
        private readonly IriConverterInterface $iriConverter,
        private readonly EntityManagerInterface $entityManager,
        private readonly TechnicianOnCallJiraCommentSyncFailureCommentFactory $commentSyncFailureCommentFactory,
        private readonly TechnicianOnCallTracteasyHelpdeskIssueCreationCommentFactory $issueCreateFailureCommentFactory,
        private readonly ResourceMetadataCollectionFactoryInterface $resourceMetadataFactory,
        private readonly JiraItemDataProvider $issueTypeProvider,
    ) {
    }

    public function __invoke(WorkerMessageFailedEvent $event): void
    {
        if ($event->willRetry()) {
            return;
        }

        $message = $event->getEnvelope()->getMessage();

        match (true) {
            $message instanceof TechnicianOnCallJiraCommentSync => $this->onCommentSyncFailed($message, $event->getThrowable()),
            $message instanceof TechnicianOnCallJiraTracteasyIssueCreate => $this->onIssueCreateFailed($message, $event->getThrowable()),
            default => null,
        };
    }

    private function onCommentSyncFailed(TechnicianOnCallJiraCommentSync $message, \Throwable $exception): void
    {
        /** @var Comment $comment */
        $comment = $this->iriConverter->getResourceFromIri($message->getCommentIri());

        /** @var TechnicianOnCall $technicianOnCall */
        $technicianOnCall = $this->iriConverter->getResourceFromIri($comment->getResource());

        $failureComment = $this->commentSyncFailureCommentFactory->create($technicianOnCall, $comment, $exception);
        $this->entityManager->persist($failureComment);
        $this->entityManager->flush();
    }

    private function onIssueCreateFailed(TechnicianOnCallJiraTracteasyIssueCreate $message, \Throwable $exception): void
    {
        /** @var TechnicianOnCall $technicianOnCall */
        $technicianOnCall = $this->iriConverter->getResourceFromIri($message->getTechnicianOnCallIri());

        /** @var User $user */
        $user = $this->iriConverter->getResourceFromIri($message->getUserIri());

        $issueOperation = $this->resourceMetadataFactory->create(TracteasyIssueType::class)->getOperation();
        $issueType = $this->issueTypeProvider->provide($issueOperation, ['id' => TracteasyIssueType::ISSUE_ISSUETYPE_ID]);

        $failureComment = $this->issueCreateFailureCommentFactory->create($technicianOnCall, $user, $issueType, $exception);
        $this->entityManager->persist($failureComment);
        $this->entityManager->flush();
    }
}
