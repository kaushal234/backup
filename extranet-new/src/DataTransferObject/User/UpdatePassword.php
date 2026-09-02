<?php

declare(strict_types=1);

namespace App\DataTransferObject\User;

final class UpdatePassword
{
    public string $password;
    public string $token;
}
