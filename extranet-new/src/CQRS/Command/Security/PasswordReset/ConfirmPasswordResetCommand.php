<?php

declare(strict_types=1);

namespace App\CQRS\Command\Security\PasswordReset;

use App\CQRS\Command\CommandInterface;

final readonly class ConfirmPasswordResetCommand implements CommandInterface
{
    public function __construct(
        public int $id,
        public string $token,
        public string $newPassword,
    ) {
    }
}
