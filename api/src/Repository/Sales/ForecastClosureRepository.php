<?php

declare(strict_types=1);

namespace App\Repository\Sales;

use App\Entity\Sales\Competitor;
use App\Entity\Sales\ForecastClosure;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ForecastClosureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ForecastClosure::class);
    }

    public function getIdentifiersForCompetitor(Competitor $competitor): array
    {
        $qb = $this->createQueryBuilder('fc');

        $qb
            ->select('fc.id')
            ->where('fc.competitor = :competitor')
            ->setParameter('competitor', $competitor)
        ;

        return $qb->getQuery()->getScalarResult();
    }
}
