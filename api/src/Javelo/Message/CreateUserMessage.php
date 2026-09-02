<?php

declare(strict_types=1);

namespace App\Javelo\Message;

use App\Javelo\Resources\User;

final class CreateUserMessage
{
    public function __construct(
        private readonly User $javeloUserToUpdate,
        private readonly array $changes,
        private readonly ?string $posterIri
    ) {
    }

    public function getJaveloUserToUpdate(): User
    {
        return $this->javeloUserToUpdate;
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
