<?php

declare(strict_types=1);

namespace App\Event\Activity;

use Symfony\Contracts\EventDispatcher\Event;

class CommentCreatedEvent extends Event
{
    use CommentEventTrait;
}
