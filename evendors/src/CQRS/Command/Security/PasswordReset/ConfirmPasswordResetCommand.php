<?php

declare(strict_types=1);

namespace App\CQRS\Command\Security\PasswordReset;

use App\CQRS\Command\CommandInterface;

final class ConfirmPasswordResetCommand implements CommandInterface
{
    public function __construct(
        public readonly int $id,
        public readonly string $token,
        public readonly string $newPassword,
    ) {
    }
}
