<?php

declare(strict_types=1);

namespace App\Repository\Quality;

use App\Entity\Directory\Location;
use App\Entity\Quality\NonConformity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NonConformityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NonConformity::class);
    }

    public function getNumberNonConformtityForLocationAndPartnumber(Location $location, array $partNumbers): array
    {
        $queryBuilder = $this->createQueryBuilder('n');
        $queryBuilder
            ->select('p.partNumber AS part_number', 'COUNT(DISTINCT n.id) AS number_ncr')
            ->join('n.parts', 'p')
            ->where('p.partNumber IN (:partNumbers)')
            ->andWhere('n.location = :location')
            ->groupBy('p.partNumber')
            ->setParameter('location', $location)
            ->setParameter('partNumbers', $partNumbers)
        ;

        $result = [];
        foreach ($queryBuilder->getQuery()->getArrayResult() as $line) {
            $result[$line['part_number']] = $line['number_ncr'];
        }

        return $result;
    }
}
