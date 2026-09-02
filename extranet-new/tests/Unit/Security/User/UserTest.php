<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security\User;

use App\Security\User\User;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function testDataTablePersistenceIdentifierIsTheUserId(): void
    {
        $user = new User(
            id: 4269,
            firstname: 'Michael (Mick)',
            lastname: 'SHUTTERS',
            identifier: 'mick@example.com',
            token: 'tok',
            passwordExpirationDate: '2099-01-01',
            language: 'en',
        );

        self::assertSame('4269', $user->getDataTablePersistenceIdentifier());
    }

    public function testDataTablePersistenceIdentifierContainsNoCacheTagReservedCharacters(): void
    {
        $user = new User(
            id: 4269,
            firstname: 'Michael (Mick)',
            lastname: 'SHUTTERS-Michael',
            identifier: 'mick@example.com',
            token: 'tok',
            passwordExpirationDate: '2099-01-01',
            language: 'en',
        );

        // Characters reserved by Symfony's cache tag system (Symfony\Component\Cache\CacheItem::RESERVED_CHARACTERS).
        self::assertFalse(strpbrk($user->getDataTablePersistenceIdentifier(), '{}()/\@:'));
    }
}
