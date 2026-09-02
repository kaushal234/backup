<?php

declare(strict_types=1);

namespace App\Agile\Event;

use App\Entity\Directory\People;

class GrantAgileGroupAccessEvent
{
    public function __construct(public readonly People $people)
    {
    }
}
