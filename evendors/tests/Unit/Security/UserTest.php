<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Security\User\Address;
use App\Security\User\User;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class UserTest extends TestCase
{
    public function testFirstnameAndLastnameAreDecoded(): void
    {
        $encodedFirstname = 'Jean &amp; Pierre';
        $encodedLastname = 'O&#39;Reilly';
        $plainFirstname = 'Marie';
        $plainLastname = 'Durand';

        $userEncoded = new User(
            id: 1,
            firstname: $encodedFirstname,
            lastname: $encodedLastname,
            identifier: 'jean.pierre@example.com',
            token: 'some-token',
            address: new Address('123 Main St', 'Apt 4B', 'Paris', 'Île-de-France', 'France', '75001'),
        );

        $userPlain = new User(
            id: 2,
            firstname: $plainFirstname,
            lastname: $plainLastname,
            identifier: 'marie.durand@example.com',
            token: 'another-token',
            address: new Address('123 Main St', 'Apt 4B', 'Paris', 'Île-de-France', 'France', '75001'),
        );

        $this->assertSame('Jean & Pierre', $userEncoded->getFirstname());
        $this->assertSame("O'Reilly", $userEncoded->getLastname());

        $this->assertSame('Marie', $userPlain->getFirstname());
        $this->assertSame('Durand', $userPlain->getLastname());
        $this->assertSame('Jean & Pierre O\'Reilly', $userEncoded->getFullname());
        $this->assertSame('Marie Durand', $userPlain->getFullname());
    }
}
