<?php

declare(strict_types=1);

namespace Shared\Provider;

use Shared\Ressources\User;

class UserProvider extends AbstractProvider
{
    public const USER_URL = '/users';

    public function findById(string $id)
    {
        $userAsArray =  $this->client->get(sprintf('%s/%d', self::USER_URL, $id));
        return $this->serializer->denormalize($userAsArray, User::class);
    }
}