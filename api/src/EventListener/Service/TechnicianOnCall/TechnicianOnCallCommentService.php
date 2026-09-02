<?php

declare(strict_types=1);

namespace App\EventListener\Service\TechnicianOnCall;

use App\Entity\Activity\Comment;
use App\Entity\Service\TechnicianOnCallCommentDiscriminator;

class TechnicianOnCallCommentService
{
    public function isFactoryFlagChangeComment(Comment $comment): bool
    {
        return
            \array_key_exists('factoryFlag', $comment->metadata)
            && \in_array(
                $comment->metadata['factoryFlag'],
                [TechnicianOnCallCommentDiscriminator::OPEN_FACTORY_FLAG->name, TechnicianOnCallCommentDiscriminator::CLOSE_FACTORY_FLAG->name],
                true)
        ;
    }
}
