<?php

declare(strict_types=1);

namespace App\Agile\Event;

use App\Agile\Resources\User;

class LogUserUpdateOnClientEvent
{
    public function __construct(
        private readonly User $user,
        private readonly array $changes
    ) {
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getChanges(): array
    {
        return $this->changes;
    }
}
