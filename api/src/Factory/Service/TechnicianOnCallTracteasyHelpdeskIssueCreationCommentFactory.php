<?php

declare(strict_types=1);

namespace App\Factory\Service;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Activity\Comment;
use App\Entity\Service\TechnicianOnCall;
use App\Entity\User;
use App\Jira\Resources\TracteasyIssueType;

class TechnicianOnCallTracteasyHelpdeskIssueCreationCommentFactory
{
    public const string COMMENT_MESSAGE_PREFIX = 'JiraTracteasy creation';

    public function __construct(
        private readonly IriConverterInterface $iriConverter,
    ) {
    }

    public function create(
        TechnicianOnCall $technicianOnCall,
        User $user,
        ?TracteasyIssueType $tracteasyIssueType = null,
        ?\Throwable $exception = null
    ): Comment {
        $message = self::COMMENT_MESSAGE_PREFIX;

        if (null !== $exception) {
            $message = \sprintf('%s failed: %s', $message, $exception->getMessage());
        }

        if (null === $tracteasyIssueType) {
            $message = \sprintf('%s failed: IssueType not available', $message);
        }

        if (null !== $technicianOnCall->jiraTracteasyIssueKey) {
            $message = \sprintf('%s success: %s', $message, $technicianOnCall->jiraTracteasyIssueKey);
        }

        $comment = new Comment();

        $comment
            ->setMessage($message)
            ->setPublic(false)
            ->setResource($this->iriConverter->getIriFromResource($technicianOnCall))
            ->setUser($user)
            ->discriminator = TechnicianOnCall::MODULE_NAME;

        return $comment;
    }

    public function createNoProjectKeyMessage(TechnicianOnCall $technicianOnCall, User $user): Comment
    {
        $comment = new Comment();

        $comment
            ->setMessage(\sprintf('%s failed: No project key available for customer %s', self::COMMENT_MESSAGE_PREFIX, $technicianOnCall->customer->getName()))
            ->setPublic(false)
            ->setResource($this->iriConverter->getIriFromResource($technicianOnCall))
            ->setUser($user)
            ->discriminator = TechnicianOnCall::MODULE_NAME;

        return $comment;
    }
}
