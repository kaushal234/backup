<?php

declare(strict_types=1);

namespace App\Repository\Parts;

use App\Entity\Parts\Courier;
use App\Entity\Parts\Tracking;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TrackingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tracking::class);
    }

    public function getIdentifiersForCourier(Courier $courier): array
    {
        $qb = $this->createQueryBuilder('t');

        $qb
            ->select('t.id')
            ->where('t.courier = :courier')
            ->setParameter('courier', $courier)
        ;

        return $qb->getQuery()->getScalarResult();
    }
}
