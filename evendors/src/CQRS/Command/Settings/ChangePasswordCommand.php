<?php

declare(strict_types=1);

namespace App\CQRS\Command\Settings;

use App\CQRS\Command\CommandInterface;

final class ChangePasswordCommand implements CommandInterface
{
    public function __construct(
        public readonly string $password,
    ) {
    }
}
