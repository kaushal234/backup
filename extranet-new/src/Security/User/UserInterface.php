<?php

declare(strict_types=1);

namespace App\Security\User;

use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

interface UserInterface extends SymfonyUserInterface
{
    public const ROLE_USER = 'ROLE_USER';

    public function getFirstname(): string;

    public function getLastname(): string;

    public function getFullname(): string;

    public function getEmail(): string;
}
