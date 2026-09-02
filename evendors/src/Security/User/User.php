<?php

declare(strict_types=1);

namespace App\Security\User;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

use const ENT_QUOTES;

final class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public readonly string $firstname;
    public readonly string $lastname;

    /**
     * @param non-empty-string $token
     */
    public function __construct(
        public readonly int $id,
        string $firstname,
        string $lastname,
        public readonly string $identifier,
        public readonly string $token,
        public readonly Address $address,
    ) {
        $this->firstname = $this->decodeIfEncoded($firstname);
        $this->lastname = $this->decodeIfEncoded($lastname);
    }

    /**
     * @return non-empty-string
     */
    public function getFullname(): string
    {
        return $this->decodeIfEncoded($this->firstname).' '.$this->decodeIfEncoded($this->lastname);
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        return [self::ROLE_USER];
    }

    public function eraseCredentials(): void
    {
        // nothing to do.
    }

    public function getAddress(): Address
    {
        return $this->address;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function getEmail(): string
    {
        return $this->identifier;
    }

    public function getPassword(): string
    {
        return '';
    }

    private function decodeIfEncoded(string $string): string
    {
        $decoded = html_entity_decode($string, ENT_QUOTES, 'UTF-8');

        return $string !== $decoded ? $decoded : $string;
    }
}
