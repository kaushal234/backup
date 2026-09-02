<?php

declare(strict_types=1);

namespace App\Tests\Mercure\Publisher;

use App\Mercure\Publisher\PublisherInterface;

class PublisherStub implements PublisherInterface
{
    public function publish(string $topic, array $data, string $type): void
    {
        // do nothing, just to avoid calling the real publisher
    }
}
