<?php

declare(strict_types=1);

namespace App\CQRS\Command\User;

use App\CQRS\Command\CommandInterface;
use App\DataTransferObject\User\UpdateUser;

class UpdateUserCommand implements CommandInterface
{
    public function __construct(
        public UpdateUser $updateUser,
    ) {
    }
}
