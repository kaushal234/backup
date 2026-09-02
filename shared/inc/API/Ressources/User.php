<?php

declare(strict_types=1);

namespace Shared\Ressources;

class User
{
    public string $username;
    public string $email;
    public string $firstname;
    public string $lastname;

    public function getFullname()
    {
        return sprintf('%s %s', $this->firstname, $this->lastname);
    }
}