<?php

declare(strict_types=1);

namespace App\Javelo\Repository;

use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\Division;
use App\Entity\Directory\People;
use App\Javelo\Resources\User;
use Doctrine\ORM\EntityManagerInterface;

class UserRepository
{
    public const CONTRACT_TYPES = [
        'Regular' => 1,
        'Apprentices' => 4,
        'Short term contract' => 2,
    ];

    public const EXCLUDE_DIVISIONS = [
        Division::INTEGRATED_THIRD_PARTIES => 6,
    ];

    public const EXCLUDE_BUSINESS_UNITS = [
        BusinessUnit::ALVEST_ARABIA_EQUIPMENT_SERVICES => 78,
        BusinessUnit::AMAL => 92,
        BusinessUnit::AGSA => 70,
    ];

    public const EXCLUDE_PEOPLE = [
        'mis.emeai.pa@tld-europe.com' => 80,
        'webmaster@tld-gse.com1' => 174,
        'yannick.leveque@tld-europe.com' => 15887,
    ];

    public const DISABLED_AT_THRESHOLD = '2024-01-01 00:00:00';

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * @return People[]
     */
    public function searchPeopleConcernedBySynchronization(?int $peopleId = null): array
    {
        return $this->entityManager->getRepository(People::class)->searchPeopleForJaveloSynchronization(self::CONTRACT_TYPES, self::EXCLUDE_DIVISIONS, self::EXCLUDE_BUSINESS_UNITS, self::EXCLUDE_PEOPLE, self::DISABLED_AT_THRESHOLD, $peopleId);
    }

    /**
     * @return People[]
     */
    public function searchPeopleWithAclAuthJavelo(?int $peopleId = null): array
    {
        return $this->entityManager->getRepository(People::class)->searchAllPeopleIdWithGroup('ACL_AUTH_JAVELO', $peopleId);
    }

    /**
     * @param User[] $userListInJavelo
     */
    public function searchJaveloUserConcerned(People $people, array $userListInJavelo): ?User
    {
        foreach ($userListInJavelo as $javeloUser) {
            if ($javeloUser->intranetId === (string) $people->getId() || $javeloUser->userName === $people->getUsername()) {
                return $javeloUser;
            }
        }

        return null;
    }
}
