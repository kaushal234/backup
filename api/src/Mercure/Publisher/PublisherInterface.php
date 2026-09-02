<?php

declare(strict_types=1);

namespace App\Mercure\Publisher;

interface PublisherInterface
{
    public function publish(string $topic, array $data, string $type): void;
}
