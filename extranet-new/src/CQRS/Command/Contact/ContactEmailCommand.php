<?php

declare(strict_types=1);

namespace App\CQRS\Command\Contact;

use App\CQRS\Command\CommandInterface;

class ContactEmailCommand implements CommandInterface
{
    public function __construct(
        public readonly string $message,
        public readonly string $receiver,
    ) {
    }
}
