<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserPasswordLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findByEmailOrUsername(string $input): array
    {
        $qb = $this->createQueryBuilder('xu');

        $qb
            ->where('xu.email = :input')
            ->orWhere('xu.username = :input')
            ->setParameter('input', $input)
        ;

        return $qb->getQuery()->getResult();
    }

    public function logPassword(User $user)
    {
        $entityManager = $this->getEntityManager();

        $log = (new UserPasswordLog())
            ->setUser($user)
            ->setEncodedPassword($user->getEncodedPassword())
            ->setSalt($user->getSalt())
        ;

        $entityManager->persist($log);
        $entityManager->flush();
    }
}
