<?php

declare(strict_types=1);

namespace App\CQRS\Command\TechnicianOnCall;

use App\CQRS\Command\CommandInterface;

class AddTechnicianOnCallCommentCommand implements CommandInterface
{
    public function __construct(
        public readonly string $iri,
        public readonly string $message,
        public readonly ?string $file,
    ) {
    }
}
