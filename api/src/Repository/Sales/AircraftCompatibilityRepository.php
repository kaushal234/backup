<?php

declare(strict_types=1);

namespace App\Repository\Sales;

use App\Entity\Sales\AircraftCompatibility\Aircraft;
use App\Entity\Sales\AircraftCompatibility\AircraftCompatibility;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AircraftCompatibilityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AircraftCompatibility::class);
    }

    public function getIdentifiersForAircraft(Aircraft $aircraft): array
    {
        $queryBuilder = $this->createQueryBuilder('ac');

        $queryBuilder
            ->select('ac.id')
            ->join('ac.aircrafts', 'a')
            ->where('a = :aircraft')
            ->setParameter('aircraft', $aircraft)
        ;

        return $queryBuilder->getQuery()->getScalarResult();
    }
}
