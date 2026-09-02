<?php

declare(strict_types=1);

namespace App\Javelo\Event;

use App\Entity\Directory\People;

class UpdateJaveloUserEvent
{
    public function __construct(private People $people, private readonly array $changes)
    {
    }

    public function getPeople(): People
    {
        return $this->people;
    }

    public function getChanges(): array
    {
        return $this->changes;
    }
}
