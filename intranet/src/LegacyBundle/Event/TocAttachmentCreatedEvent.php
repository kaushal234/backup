<?php

declare(strict_types=1);

namespace LegacyBundle\Event;

use Symfony\Contracts\EventDispatcher\Event as EventDipatcherEvent;

class TocAttachmentCreatedEvent extends EventDipatcherEvent
{
    /** @var string */
    protected $filePath;

    /** @var array */
    protected $tocData;

    /** @var array */
    protected $updatedMetadata = [];

    public function __construct(string $filePath, array $tocData)
    {
        $this->filePath = $filePath;
        $this->tocData = $tocData;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
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
