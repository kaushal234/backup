<?php

declare(strict_types=1);

namespace LegacyBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class PersistEvent extends Event
{
    private $object;

    public function __construct($object)
    {
        $this->object = $object;
    }

    public function getObject()
    {
        return $this->object;
    }
}
