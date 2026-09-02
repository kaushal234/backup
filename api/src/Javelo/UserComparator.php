<?php

declare(strict_types=1);

namespace App\Javelo;

use App\Javelo\Resources\User;

class UserComparator
{
    private const array INIT_ONLY_PROPERTIES = ['locale'];

    /** Fields that cannot be cleared via sync. */
    private const array NON_CLEARABLE_FIELDS = ['gender', 'lastExitDate'];

    /**
     * Get the list of changes between updatedPeople and previousJaveloUser objects using reflection.
     *
     * @return array the list of fields that are different
     */
    public function getChanges(User $updatedJaveloUser, ?User $previousJaveloUser): array
    {
        $previousJaveloUser = $previousJaveloUser ?: new User();

        $reflectionClass = new \ReflectionClass(User::class);
        $properties = $reflectionClass->getProperties();
        $changes = [];

        foreach ($properties as $property) {
            $propertyName = $property->getName();

            if (\in_array($propertyName, self::INIT_ONLY_PROPERTIES, true)) {
                continue;
            }

            $updatedValue = $property->getValue($updatedJaveloUser);
            $previousValue = $property->getValue($previousJaveloUser);

            if (
                \in_array($propertyName, self::NON_CLEARABLE_FIELDS, true)
                && (null === $updatedValue || '' === $updatedValue)
            ) {
                continue;
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
