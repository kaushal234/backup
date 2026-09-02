<?php

declare(strict_types=1);

namespace App\CQRS\Command\User;

use App\CQRS\Command\CommandInterface;

final class UpdatePasswordCommand implements CommandInterface
{
    public function __construct(
        public readonly string $password,
        public readonly string $token,
    ) {
    }
}
