<?php

declare(strict_types=1);

namespace LegacyBundle\Event;

use Symfony\Contracts\EventDispatcher\Event as EventDipatcherEvent;

class TocCommentPulledEvent extends EventDipatcherEvent
{
    /** @var array */
    protected $pulledData;

    /** @var array */
    protected $commentData = [];

    public function __construct(array $pulledData)
    {
        $this->pulledData = $pulledData;
    }

    public function getPulledData(): array
    {
        return $this->pulledData;
    }

    public function getCommentData(): array
    {
        return $this->commentData;
    }

    public function setCommentData(array $commentData): void
    {
        $this->commentData = $commentData;
    }
}
