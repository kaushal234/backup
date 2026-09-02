<?php

declare(strict_types=1);

namespace App\Message\Directory;

class GroupPositionUpdate
{
    public function __construct(
        private readonly string $position,
        private readonly string $division,
        private readonly array $groupToAdd = [],
        private readonly array $groupToDelete = [],
    ) {
    }

    public function getGroupToAdd(): array
    {
        return $this->groupToAdd;
    }

    public function getGroupToDelete(): array
    {
        return $this->groupToDelete;
    }

    public function getPosition(): string
    {
        return $this->position;
    }

    public function getDivision(): string
    {
        return $this->division;
    }
}
