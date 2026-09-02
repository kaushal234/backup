<?php

declare(strict_types=1);

namespace App\Event\Activity;

use App\Entity\Activity\Comment;

trait CommentEventTrait
{
    private Comment $comment;
    private object $item;
    private bool $mainRequest;

    public function __construct(Comment $comment, object $item, bool $mainRequest)
    {
        $this->comment = $comment;
        $this->item = $item;
        $this->mainRequest = $mainRequest;
    }

    public function getComment(): Comment
    {
        return $this->comment;
    }

    public function getItem(): object
    {
        return $this->item;
    }

    public function isMainRequest(): bool
    {
        return $this->mainRequest;
    }
}
