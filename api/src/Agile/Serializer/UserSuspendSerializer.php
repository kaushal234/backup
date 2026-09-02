<?php

declare(strict_types=1);

namespace App\Agile\Serializer;

use App\Agile\Resources\User;

class UserSuspendSerializer
{
    public function serialize(User $user): array
    {
        if (empty($user->peopleId)) {
            throw new \InvalidArgumentException('The peopleId cannot be empty.');
        }

        return [
            'ref' => (string) $user->peopleId,
        ];
    }
}
