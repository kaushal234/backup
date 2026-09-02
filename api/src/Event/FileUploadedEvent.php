<?php

declare(strict_types=1);

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class FileUploadedEvent extends Event
{
    private readonly object $object;
    private readonly array $metadata;

    public function __construct(object $object, array $metadata)
    {
        $this->object = $object;
        $this->metadata = $metadata;
    }

    public function getObject(): object
    {
        return $this->object;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }
}
