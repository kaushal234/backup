<?php

declare(strict_types=1);

namespace App\Agile;

use App\Agile\Resources\User;

class UserEventResolver
{
    public const USER_JOINED = 'user_joined';
    public const USER_UPDATED = 'user_updated';
    public const USER_SUSPENDED = 'user_suspended';
    private ?string $resolvedEvent;

    public function __construct(
        private readonly ?User $previousAgileUser,
        private readonly array $changes,
        private readonly bool $isEligibleForSync
    ) {
        $this->resolvedEvent = $this->resolveEvent();
    }

    public function getResolvedEvent(): ?string
    {
        return $this->resolvedEvent;
    }

    private function resolveEvent(): ?string
    {
        if ($this->isSuspendedEvent()) {
            return self::USER_SUSPENDED;
        }

        if ($this->isJoinedEvent()) {
            return self::USER_JOINED;
        }

        if ($this->isUpdatedEvent()) {
            return self::USER_UPDATED;
        }

        return null;
    }

    /**
     * A user is considered "joined"
     * if they are eligible for synchronization (i.e., concerned and active)
     * and the previous Agile user is either null or was inactive.
     */
    private function isJoinedEvent(): bool
    {
        return $this->isEligibleForSync && (!$this->previousAgileUser instanceof User || (!$this->previousAgileUser->isActive()));
    }

    /**
     * A user is considered "updated"
     * if they are eligible for synchronization (i.e., concerned and active),
     * and there are changes,
     * and they were previously active on Agile.
     */
    private function isUpdatedEvent(): bool
    {
        return $this->isEligibleForSync && ($this->previousAgileUser instanceof User && $this->previousAgileUser->isActive() && !empty($this->changes));
    }

    /**
     * A user is considered "suspended"
     * if they are no longer eligible for synchronization (i.e., no longer concerned or inactive),
     * and they were previously active on Agile.
     */
    private function isSuspendedEvent(): bool
    {
        return !$this->isEligibleForSync && ($this->previousAgileUser instanceof User && $this->previousAgileUser->isActive());
    }
}
