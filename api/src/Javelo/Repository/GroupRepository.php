<?php

declare(strict_types=1);

namespace App\Javelo\Repository;

class GroupRepository
{
    public const ADD_MEMBERS_KEY = 'addMembers';
    public const REMOVE_MEMBERS_KEY = 'removeMembers';
    private ?array $groups = null;
    /** @var array<string, array{businessUnit: string, division: ?string}> */
    private array $missingGroups = [];

    public function __construct(
        private readonly GroupClientRepository $groupClientRepository,
    ) {
    }

    public function getGroups(): ?array
    {
        return $this->groups;
    }

    public function initializeGroups(): void
    {
        if (null === $this->groups) {
            $this->groups = $this->groupClientRepository->getAllGroups();
        }
    }

    public function addUserOnAddMembersList(string $javeloGroupName, string $javeloUserId): void
    {
        if (isset($this->groups[$javeloGroupName]) && !\in_array($javeloUserId, $this->groups[$javeloGroupName][self::ADD_MEMBERS_KEY], true)) {
            $this->groups[$javeloGroupName][self::ADD_MEMBERS_KEY][] = $javeloUserId;
        }
    }

    public function addUserOnRemoveMembersList(string $javeloGroupName, string $javeloUserId): void
    {
        if (isset($this->groups[$javeloGroupName]) && !\in_array($javeloUserId, $this->groups[$javeloGroupName][self::REMOVE_MEMBERS_KEY], true)) {
            $this->groups[$javeloGroupName][self::REMOVE_MEMBERS_KEY][] = $javeloUserId;
        }
    }

    public function addMissingGroup(string $javeloGroupName, string $businessUnit, ?string $division): void
    {
        if (!isset($this->missingGroups[$javeloGroupName])) {
            $this->missingGroups[$javeloGroupName] = [
                'businessUnit' => $businessUnit,
                'division' => $division,
            ];
        }
    }

    /**
     * @return array<string, array{businessUnit: string, division: ?string}>
     */
    public function getMissingGroups(): array
    {
        return $this->missingGroups;
    }
}
