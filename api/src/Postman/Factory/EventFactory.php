<?php

declare(strict_types=1);

namespace App\Postman\Factory;

use App\Postman\Resource\Event;

class EventFactory
{
    public function __construct(
        private readonly ScriptFactory $scriptFactory
    ) {
    }

    public function create(): Event
    {
        $postmanEvent = new Event();
        $postmanEvent->listen = 'test';
        $postmanEvent->script = $this->scriptFactory->create();

        return $postmanEvent;
    }
}
