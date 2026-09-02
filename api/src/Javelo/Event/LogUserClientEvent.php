<?php

declare(strict_types=1);

namespace App\Javelo\Event;

use App\Entity\Directory\People;
use App\Javelo\Resources\User;

class LogUserClientEvent
{
    public function __construct(private readonly User $javeloUser, private readonly array $changes, private readonly ?People $poster)
    {
    }

    public function getJaveloUser(): User
    {
        return $this->javeloUser;
    }

    public function getChanges(): array
    {
        return $this->changes;
    }

    public function getPoster(): ?People
    {
        return $this->poster;
    }
}
