<?php

declare(strict_types=1);

namespace App\Test\Helpers;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait KernelBrowserTrait
{
    use ContainerTrait;

    /**
     * Shortcut to log a given user in a kernel browser client.
     */
    public static function loginUser(KernelBrowser $client, string $identifier = 'azjezz'): void
    {
        $user = self::getUserProvider()->getUser($identifier);
        $client->loginUser($user);
    }
}
