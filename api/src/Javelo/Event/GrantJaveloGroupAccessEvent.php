<?php

declare(strict_types=1);

namespace App\Javelo\Event;

use App\Entity\Directory\People;

class GrantJaveloGroupAccessEvent
{
    public function __construct(public readonly People $people)
    {
    }
}
