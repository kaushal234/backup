<?php

declare(strict_types=1);

namespace App\Agile;

use App\Agile\Factory\UserFactory;
use App\Agile\Resources\User;
use App\Entity\Directory\People;

class UserSyncContext
{
    private array $changes = [];
    private ?User $agileUserToUpdate = null;

    private ?UserEventResolver $userEventResolver = null;

    public function __construct(
        private readonly UserFactory $userFactory,
        private readonly UserComparator $userComparator
    ) {
    }

    public function getChanges(): array
    {
        return $this->changes;
    }

    public function getAgileUserToUpdate(): User
    {
        return $this->agileUserToUpdate;
    }

    public function getResolvedEvent(): ?string
    {
        return $this->userEventResolver->getResolvedEvent();
    }

    public function buildContext(People $apiUser, bool $isEligibleForSync, array $agileUsers, ?User $previousAgileUser = null): self
    {
        $this->agileUserToUpdate = $this->userFactory->createFromPeople($apiUser, null === $previousAgileUser, $agileUsers);
        $this->changes = $this->userComparator->getChanges($this->agileUserToUpdate, $previousAgileUser);
        $this->userEventResolver = new UserEventResolver($previousAgileUser, $this->changes, $isEligibleForSync);

        return $this;
    }
}
