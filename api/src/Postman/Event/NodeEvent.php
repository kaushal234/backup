<?php

declare(strict_types=1);

namespace App\Postman\Event;

use App\Postman\Resource\Node;
use Symfony\Component\Routing\Route;

class NodeEvent
{
    public function __construct(
        public readonly Node $node,
        public readonly Route $route,
        public readonly string $method,
    ) {
    }
}
