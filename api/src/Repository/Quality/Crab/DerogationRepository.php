<?php

declare(strict_types=1);

namespace App\Repository\Quality\Crab;

use App\Entity\Quality\Derogation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DerogationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Derogation::class);
    }

    public function findExpiredDerogation()
    {
        $qb = $this->createQueryBuilder('q');

        return $qb
            ->where('q.dueDate < :today')
            ->andWhere('q.status = :open')
            ->setParameter('today', (new \DateTime())->format('Y-m-d'))
            ->setParameter('open', Derogation::OPEN)
            ->getQuery()
            ->getResult()
        ;
    }
}
