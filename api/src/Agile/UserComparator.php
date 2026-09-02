<?php

declare(strict_types=1);

namespace App\Agile;

use App\Agile\Resources\User;

class UserComparator
{
    /**
     * startDate and timeZone only use on creation
     * we compare managerPeopleId not managerAgileId.
     */
    private const IGNORED_VALUES = [
        'startDate',
        'timeZone',
        'loginMethod',
    ];

    /**
     * Get the list of changes between updatedAgileUser and previousAgileUser objects using reflection.
     *
     * @return array the list of fields that are different
     */
    public function getChanges(User $updatedAgileUser, ?User $previousAgileUser): array
    {
        $reflectionClass = new \ReflectionClass(User::class);
        $properties = $reflectionClass->getProperties();
        $changes = [];

        foreach ($properties as $property) {
            $propertyName = $property->getName();

            if (\in_array($propertyName, self::IGNORED_VALUES, true)) {
                continue;
            }

            $updatedValue = $property->getValue($updatedAgileUser);
            $previousValue = null === $previousAgileUser ? '' : $property->getValue($previousAgileUser);

            // agile email are in lower case not email on intranet
            if ('email' === $propertyName) {
                $updatedValue = mb_strtolower((string) $updatedValue);
                $previousValue = mb_strtolower((string) $previousValue);
            }

            if ($updatedValue !== $previousValue) {
                $changes[$propertyName] = [
                    $previousValue,
                    $updatedValue,
                ];
            }
        }

        return $changes;
    }
}
