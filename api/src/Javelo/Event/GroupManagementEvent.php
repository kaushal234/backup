<?php

declare(strict_types=1);

namespace App\Javelo\Event;

use App\Javelo\Resources\User;

class GroupManagementEvent
{
    public function __construct(private readonly User $javeloUser)
    {
    }

    public function getJaveloUser(): User
    {
        return $this->javeloUser;
    }
}
