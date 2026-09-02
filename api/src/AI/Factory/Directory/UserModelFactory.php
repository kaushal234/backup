<?php

declare(strict_types=1);

namespace App\AI\Factory\Directory;

use App\AI\Dto\Directory\PeopleModel;
use App\Entity\User;
use LegacyBundle\Entity\Directory\AbstractPeople as LegacyPeople;

final class UserModelFactory
{
    /**
     * Accepts any modern {@see User} (People, ExtranetUser, …) or a legacy people entity.
     */
    public function create(User|LegacyPeople $people): PeopleModel
    {
        if ($people instanceof LegacyPeople) {
            return new PeopleModel(
                username: $people->getUsername(),
                email: $people->email,
                firstname: $people->firstname,
                lastname: $people->lastname,
            );
        }

        return new PeopleModel(
            username: $people->getUsername(),
            email: $people->getEmail(),
            firstname: $people->getFirstname(),
            lastname: $people->getLastname(),
        );
    }
}
