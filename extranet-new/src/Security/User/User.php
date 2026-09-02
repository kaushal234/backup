<?php

declare(strict_types=1);

namespace App\Security\User;

use App\Sdk\Resource\Customer;
use App\Sdk\Resource\ExtranetUserAcl;
use Kreyu\Bundle\DataTableBundle\Persistence\PersistenceSubjectInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

final class User implements UserInterface, PasswordAuthenticatedUserInterface, PersistenceSubjectInterface
{
    /**
     * @param non-empty-string       $token
     * @param array<ExtranetUserAcl> $acls
     */
    public function __construct(
        public readonly int $id,
        public readonly string $firstname,
        public readonly string $lastname,
        public readonly string $identifier,
        public readonly string $token,
        public readonly string $passwordExpirationDate,
        public readonly ?string $language,
        public readonly array $acls = [],
    ) {
    }

    /**
     * @return non-empty-string
     */
    public function getFullname(): string
    {
        return $this->firstname.' '.$this->lastname;
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

    public function getPasswordExpirationDate(): \DateTime
    {
        return new \DateTime($this->passwordExpirationDate);
    }

    public function isPasswordExpired(): bool
    {
        $now = new \DateTimeImmutable('now');

        return $this->getPasswordExpirationDate() <= $now;
    }

    /**
     * @return array<Customer>
     */
    public function getCustomers(): array
    {
        $customers = array_reduce($this->acls, static function ($memo, ExtranetUserAcl $acl) {
            if (isset($memo[$acl->customerRelationshipTeam->customer->getIri()])) {
                return $memo;
            }

            $memo[$acl->customerRelationshipTeam->customer->getIri()] = $acl->customerRelationshipTeam->customer;

            return $memo;
        }, []);

        return array_values($customers);
    }

    public function getDataTablePersistenceIdentifier(): string
    {
        // Must not contain the characters reserved by Symfony's cache tag system
        // ("{}()/\@:"), which a display name may carry (e.g. "SHUTTERS-Michael (Mick)").
        // The immutable user id is unique, stable and safe.
        return (string) $this->id;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }
}
