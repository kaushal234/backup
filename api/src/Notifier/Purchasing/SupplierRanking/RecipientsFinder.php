<?php

declare(strict_types=1);

namespace App\Notifier\Purchasing\SupplierRanking;

use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Repository\Directory\PeopleRepository;
use Doctrine\ORM\EntityManagerInterface;

class RecipientsFinder
{
    private readonly EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findDecreasedClassificationRecipients(Location $location): array
    {
        /** @var PeopleRepository $peopleRepository */
        $peopleRepository = $this->entityManager->getRepository(People::class);

        return [
            ...$peopleRepository->findGroupMembers('ROLE_MLM', $location),
            ...$peopleRepository->findGroupMembers('ROLE_QAM', $location),
            ...$peopleRepository->findGroupMembers('ROLE_COO', $location),
            ...$peopleRepository->findGroupMembers('ROLE_CPO'),
            ...$peopleRepository->findGroupMembers('ROLE_TCEO'),
            ...$peopleRepository->findGroupMembers('ROLE_GCEO'),
        ];
    }

    public function findReviewRecipients(Location $location): array
    {
        /** @var PeopleRepository $peopleRepository */
        $peopleRepository = $this->entityManager->getRepository(People::class);

        return [
            ...$peopleRepository->findGroupMembers('ROLE_MLM', $location),
        ];
    }

    public function findReviewCc(Location $location): array
    {
        /** @var PeopleRepository $peopleRepository */
        $peopleRepository = $this->entityManager->getRepository(People::class);

        return [
            ...array_reduce($peopleRepository->findGroupMembers('ROLE_QAM', $location), static function ($memo, People $people) {
                $memo[] = $people->getEmail();

                return $memo;
            }, []),
        ];
    }
}
