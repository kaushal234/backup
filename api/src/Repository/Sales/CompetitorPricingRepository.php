<?php

declare(strict_types=1);

namespace App\Repository\Sales;

use App\Entity\Sales\Competitor;
use App\Entity\Sales\CompetitorPricing;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CompetitorPricingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompetitorPricing::class);
    }

    public function getIdentifiersForCompetitor(Competitor $competitor): array
    {
        $qb = $this->createQueryBuilder('cp');

        $qb
            ->select('cp.id')
            ->where('cp.competitor = :competitor')
            ->setParameter('competitor', $competitor)
        ;

        return $qb->getQuery()->getScalarResult();
    }
}
