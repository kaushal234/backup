<?php

declare(strict_types=1);

namespace App\CQRS\Command\Security\PasswordReset;

use App\CQRS\Command\CommandInterface;

final class SendPasswordResetEmailCommand implements CommandInterface
{
    public function __construct(
        public readonly string $email,
    ) {
    }
}
