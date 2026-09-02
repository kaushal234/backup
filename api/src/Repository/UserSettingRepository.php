<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Directory\People;
use App\Entity\UserSetting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserSettingRepository extends ServiceEntityRepository
{
    public function __construct(
        protected ManagerRegistry $registry,
    ) {
        parent::__construct($registry, UserSetting::class);
    }

    public function deleteAllForPeople(People $people): void
    {
        $this->createQueryBuilder('us')
            ->delete()
            ->where('us.user = :user')
            ->setParameter('user', $people)
            ->getQuery()
            ->execute();
    }

    public function findByKey(string $key)
    {
        $queryBuilder = $this->createQueryBuilder('us');

        return $queryBuilder
            ->join('us.user', 'user')
            ->where('us.name = :key')
            ->andWhere('user.disabled = false')
            ->andWhere('user.hidden = false')
            ->setParameter('key', $key)
            ->getQuery()
            ->getResult()
        ;
    }
}
