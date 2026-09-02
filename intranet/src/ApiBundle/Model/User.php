<?php

declare(strict_types=1);

namespace ApiBundle\Model;

use ApiBundle\Iri\Iri;
use Kreyu\Bundle\DataTableBundle\Persistence\PersistenceSubjectInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface, PasswordAuthenticatedUserInterface, EquatableInterface, PersistenceSubjectInterface
{
    final public const ROLE_PASSWORD_NOT_EXPIRED = 'ROLE_PASSWORD_NOT_EXPIRED';
    final public const ROLE_IMPERSONATED = 'ROLE_IMPERSONATED';

    /** @var string|null */
    private $id;

    public function __construct(
        public readonly string $iriId,
        public readonly string $iriType,
        public readonly string $username,
        public readonly string $firstname,
        public readonly string $lastname,
        public readonly bool $disabled,
        public readonly bool $hidden,
        public readonly string $expirationDate,
        public readonly array $photo,
        public readonly array $roles,
        public readonly array $acls,
        public readonly ?BusinessUnit $businessUnit,
        public readonly string $token,
    ) {
        $this->id = Iri::id($iriId);
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function getPassword(): ?string
    {
        return 'null';
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function eraseCredentials(): void
    {
    }

    public function isAccountNonExpired(): bool
    {
        return true;
    }

    public function isAccountNonLocked(): bool
    {
        return true;
    }

    public function isCredentialsNonExpired(): bool
    {
        return true;
    }

    public function isEnabled(): bool
    {
        return !$this->disabled;
    }

    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getIriId(): string
    {
        return $this->iriId;
    }

    public function getIriType(): string
    {
        return $this->iriType;
    }

    public function getExpirationDate(): \DateTime
    {
        return new \DateTime($this->expirationDate);
    }

    public function getTimeBeforePasswordExpiration(): \DateInterval
    {
        return (new \DateTime())->diff($this->getExpirationDate());
    }

    public function getPhoto(): array
    {
        return $this->photo;
    }

    public function getAcls(): array
    {
        return $this->acls;
    }

    public function getBusinessUnit(): ?BusinessUnit
    {
        return $this->businessUnit;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function isEqualTo(UserInterface $user): bool
    {
        if ($this->username !== $user->getUserIdentifier()) {
            return false;
        }

        return true;
    }

    public function getDataTablePersistenceIdentifier(): string
    {
        return (string) $this->id;
    }
}
