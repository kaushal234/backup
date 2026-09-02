<?php

declare(strict_types=1);

namespace App\Agile\Serializer;

use App\Agile\Resources\User;

class UserPostSerializer
{
    public function serialize(User $user): array
    {
        if (empty($user->peopleId)) {
            throw new \InvalidArgumentException('The peopleId cannot be empty.');
        }

        $initializeData = [];
        if ($user->isNew) {
            $initializeData = [
                'startDate' => $user->getFormatedStartDate(\DateTime::ATOM),
                'timeZone' => $user->timeZone,
            ];
        }

        return $initializeData + [
            'ref' => (string) $user->peopleId,
            'email' => $user->email,
            'firstName' => $user->firstName,
            'lastName' => $user->lastName,
            'jobTitle' => $user->jobTitle,
            // if manager is not on agile we can t set it on agile
            'managerRef' => '' !== $user->managerAgileId ? $user->managerPeopleId : '',
            'languageCode' => $user->languageCode,
            'position' => $user->position,
            'positioncategory' => $user->positionCategory,
            'department' => $user->department,
            'address' => mb_trim($user->address),
            'contracttype' => $user->contracttype,
            'street1' => $user->street1,
            'street2' => $user->street2,
            'city' => $user->city,
            'country' => $user->country,
            'zipcode' => $user->zipcode,
            'region' => $user->region,
            'businessunit' => $user->businessunit,
            'subdivision' => $user->subdivision,
            'division' => $user->division,

            'loginMethod' => $user->loginMethod,
        ];
    }
}
