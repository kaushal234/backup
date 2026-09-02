<?php

declare(strict_types=1);

namespace App\Javelo\Event;

use App\Javelo\Resources\User;

class UserCreatedEvent
{
    public function __construct(
        private readonly User $javeloUser,
        private readonly array $changes,
        private readonly ?string $posterIri = null,
    ) {
    }

    public function getJaveloUser(): User
    {
        return $this->javeloUser;
    }

    public function getChanges(): array
    {
        return $this->changes;
    }

    public function getPosterIri(): ?string
    {
        return $this->posterIri;
    }
}
