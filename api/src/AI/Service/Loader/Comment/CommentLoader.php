<?php

declare(strict_types=1);

namespace App\AI\Service\Loader\Comment;

use App\AI\Dto\Activity\CommentModel;
use App\Entity\Activity\Comment;
use App\Repository\Common\CommentRepository;

class CommentLoader
{
    public function __construct(
        private readonly CommentRepository $commentRepository,
    ) {
    }

    /**
     * @return CommentModel[]
     */
    public function findComments(object $entity): array
    {
        $comments = [];
        /** @var Comment $comment */
        foreach ($this->commentRepository->findCommentsForEntity($entity) as $comment) {
            $message = mb_trim((string) $comment->getMessage());
            if ('' === $message) {
                continue;
            }

            $author = $comment->getUser();
            $comments[] = new CommentModel(
                message: $message,
                createdAt: $comment->getCreatedAt(),
                authorEmail: $author?->getEmail(),
                authorFirstname: $author?->getFirstname(),
                authorLastname: $author?->getLastname(),
            );
        }

        return $comments;
    }
}
