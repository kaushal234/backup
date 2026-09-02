<?php

declare(strict_types=1);

namespace App\MessageHandler\Service;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use App\Entity\Activity\Comment;
use App\Entity\Service\TechnicianOnCall;
use App\Jira\DataProcessor\JiraDataProcessor;
use App\Jira\Resources\JiraIssueComment;
use App\Message\Service\TechnicianOnCallJiraCommentSync;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class TechnicianOnCallJiraCommentSyncHandler
{
    public function __construct(
        private readonly IriConverterInterface $iriConverter,
        private readonly ResourceMetadataCollectionFactoryInterface $resourceMetadataFactory,
        private readonly JiraDataProcessor $processor,
    ) {
    }

    public function __invoke(TechnicianOnCallJiraCommentSync $message): void
    {
        /** @var Comment $comment */
        $comment = $this->iriConverter->getResourceFromIri($message->getCommentIri());

        /** @var TechnicianOnCall $technicianOnCall */
        $technicianOnCall = $this->iriConverter->getResourceFromIri($comment->getResource());

        if (null === $technicianOnCall->jiraTracteasyIssueKey) {
            return;
        }

        $issueComment = new JiraIssueComment($technicianOnCall->jiraTracteasyIssueKey);
        $issueComment->body = $comment->getMessage();
        $issueComment->public = $comment->isPublic();

        $operation = $this->resourceMetadataFactory->create(JiraIssueComment::class)->getOperation('jira_tracteasy_post_comment');

        $this->processor->process($issueComment, $operation);
    }
}
