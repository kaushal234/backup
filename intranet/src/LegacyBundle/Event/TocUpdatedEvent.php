<?php

declare(strict_types=1);

namespace LegacyBundle\Event;

use Symfony\Contracts\EventDispatcher\Event as EventDipatcherEvent;

class TocUpdatedEvent extends EventDipatcherEvent
{
    /** @var array */
    protected $tocData;

    /** @var array */
    protected $updatedMetadata;

    public function __construct(array $data = [])
    {
        $this->tocData = $data;
    }

    public function getTocData(): array
    {
        return $this->tocData;
    }

    public function getUpdatedMetadata(): array
    {
        return $this->updatedMetadata;
    }

    public function setUpdatedMetadata(array $updatedMetadata): void
    {
        $this->updatedMetadata = $updatedMetadata;
    }
}
