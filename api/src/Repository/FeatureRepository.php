<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Directory\People;
use App\Entity\Feature;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FeatureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Feature::class);
    }

    public function loadFeaturesByPeople(People $people)
    {
        return $this->createQueryBuilder('f')
            ->select('f.name', 'l.id AS location_id')
            ->distinct(true)
            ->innerJoin('f.groups', 'g')
            ->innerJoin('g.acls', 'a')
            ->leftJoin('a.location', 'l')
            ->where('a.user = :user')
            ->setParameter(':user', $people)
            ->getQuery()
            ->getResult();
    }
}
