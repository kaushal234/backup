<?php

declare(strict_types=1);

namespace App\Event;

use App\Doctrine\Change;
use Symfony\Contracts\EventDispatcher\Event;

class EntityChangeEvent extends Event
{
    private readonly Change $change;

    /**
     * EntityChangeEvent constructor.
     */
    public function __construct(Change $change)
    {
        $this->change = $change;
    }

    /**
     * @return Change
     */
    public function getChange()
    {
        return $this->change;
    }
}
