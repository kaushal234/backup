<?php

declare(strict_types=1);

namespace App\CQRS\Command\Service;

use App\CQRS\Command\CommandInterface;

readonly class TechnicianOnCallSatisfactionCommand implements CommandInterface
{
    public function __construct(
        public int $execution,
        public int $responsiveness,
        public int $communication,
        public int $attitude,
        public string $comment,
        public string $technicianOnCall,
        public string $token,
    ) {
    }
}
