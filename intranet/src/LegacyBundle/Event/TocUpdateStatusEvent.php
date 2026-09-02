<?php

declare(strict_types=1);

namespace LegacyBundle\Event;

use Symfony\Contracts\EventDispatcher\Event as EventDipatcherEvent;

class TocUpdateStatusEvent extends EventDipatcherEvent
{
    /** @var string */
    protected $content;

    /** @var array */
    protected $tocData;

    /** @var array */
    protected $updatedMetadata;

    public function __construct(string $content, array $data = [])
    {
        $this->content = $content;
        $this->tocData = $data;
    }

    public function getContent(): string
    {
        return $this->content;
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
