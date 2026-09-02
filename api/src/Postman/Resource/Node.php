<?php

declare(strict_types=1);

namespace App\Postman\Resource;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Node
{
    public string $name;
    public ?Collection $event = null;
    public ?Collection $item = null;
    public Request $request;
    public Response $response;

    public function addEvent(Event $script): void
    {
        if (null === $this->event) {
            $this->event = new ArrayCollection();
        }

        $this->event->add($script);
    }
}
