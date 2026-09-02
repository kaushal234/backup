<?php

declare(strict_types=1);

namespace LegacyBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class UpdateEvent extends Event
{
    private $object;
    private readonly array $changeSet;

    public function __construct($object, array $changeSet)
    {
        $this->object = $object;
        $this->changeSet = $changeSet;
    }

    public function getObject()
    {
        return $this->object;
    }

    public function getChangeSet()
    {
        return $this->changeSet;
    }
}
