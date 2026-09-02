<?php

declare(strict_types=1);

namespace App\Factory\Service;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Activity\Comment;
use App\Entity\Service\TechnicianOnCall;

class TechnicianOnCallJiraCommentSyncFailureCommentFactory
{
    public const string COMMENT_MESSAGE_PREFIX = 'JiraTracteasy comment sync';

    public function __construct(
        private readonly IriConverterInterface $iriConverter,
    ) {
    }

    public function create(TechnicianOnCall $technicianOnCall, Comment $syncedComment, \Throwable $exception): Comment
    {
        $comment = new Comment();

        $comment
            ->setMessage(\sprintf('%s failed: %s', self::COMMENT_MESSAGE_PREFIX, $exception->getMessage()))
            ->setPublic(false)
            ->setResource($this->iriConverter->getIriFromResource($technicianOnCall))
            ->setUser($syncedComment->getUser())
            ->discriminator = TechnicianOnCall::MODULE_NAME;

        return $comment;
    }
}
