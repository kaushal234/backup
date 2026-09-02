<?php

declare(strict_types=1);

namespace App\Javelo\Event;

class GroupUpdateEvent
{
    public function __construct(
        private readonly bool $sendMissingGroupMail = false,
    ) {
    }

    public function shouldSendMissingGroupMail(): bool
    {
        return $this->sendMissingGroupMail;
    }
}
