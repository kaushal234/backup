<?php

declare(strict_types=1);

namespace App\Mercure\Publisher;

use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

final readonly class HubPublisher implements PublisherInterface
{
    public function __construct(
        private HubInterface $hub,
    ) {
    }

    public function publish(string $topic, array $data, string $type, bool $private = true): void
    {
        $this->hub->publish(new Update(topics: $topic, data: json_encode($data, \JSON_THROW_ON_ERROR), private: $private, type: $type));
    }
}
