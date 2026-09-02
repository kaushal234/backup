<?php

declare(strict_types=1);

namespace App\Repository\Sales;

use App\Entity\Directory\Location;
use App\Entity\Sales\EquipmentShippingRecord\PlanningDailyLimit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

class PlanningDailyLimitRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
    ) {
        parent::__construct($registry, PlanningDailyLimit::class);
    }

    public function findPlanningDailyLimitByFactoryId(int $factoryId): ?PlanningDailyLimit
    {
        $qb = $this->createQueryBuilder('p');

        $result = $qb
            ->leftJoin(Location::class, 'factory', Join::WITH, 'p.factory = factory')
            ->where($qb->expr()->eq('factory.id', ':id'))
            ->setMaxResults(1)
            ->setParameter('id', $factoryId)
            ->getQuery()
            ->getResult()
        ;

        return $result[0] ?? null;
    }
}
