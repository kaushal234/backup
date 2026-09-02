<?php

declare(strict_types=1);

namespace App\Test\Helpers;

use App\Test\Security\User\UserProvider;

trait ContainerTrait
{
    /**
     * Get the UserProviders service with the correct typehint.
     */
    public static function getUserProvider(): UserProvider
    {
        /** @var UserProvider $userProvider */
        $userProvider = self::getContainer()->get(UserProvider::class);

        return $userProvider;
    }
}
