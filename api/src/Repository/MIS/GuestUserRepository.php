<?php

declare(strict_types=1);

namespace App\Repository\MIS;

use App\Entity\MIS\GuestUser\GuestUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class GuestUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GuestUser::class);
    }

    public function findUsersToActivate()
    {
        $qb = $this->createQueryBuilder('g');
        $qb
            ->where('g.enableAt <= :now')
            ->andWhere('g.plannedDisableAt >= :now')
            ->andWhere('g.disabled = true')
            ->andWhere('g.hidden = true')
            ->setParameter('now', new \DateTime())
        ;

        return $qb->getQuery()->getResult();
    }

    public function enableAndUnhidePeople(GuestUser $guest)
    {
        $guest->setDisabled(false);
        $guest->setHidden(false);

        $this->getEntityManager()->persist($guest);
        $this->getEntityManager()->flush();
    }

    public function findExpiredInOneMonth(): array
    {
        $queryBuilder = $this->createQueryBuilder('g');

        return $queryBuilder
            ->where('CAST(g.plannedDisableAt as date) = CAST(:date as date)')
            ->andWhere('g.disabled = false')
            ->andWhere('g.hidden = false')
            ->setParameter('date', (new \DateTime())->add(new \DateInterval('P1M')))
            ->getQuery()
            ->getResult();
    }

    public function findUsersToDisable()
    {
        $qb = $this->createQueryBuilder('g');
        $qb
            ->where('g.plannedDisableAt < :now')
            ->andWhere('g.disabled = false')
            ->setParameter('now', new \DateTime())
        ;

        return $qb->getQuery()->getResult();
    }

    public function disableAndHideGuest(GuestUser $guest)
    {
        // setDisabled(true) also forces hidden = true (see User entity)
        $guest->setDisabled(true);

        $this->getEntityManager()->persist($guest);
        $this->getEntityManager()->flush();
    }
}
