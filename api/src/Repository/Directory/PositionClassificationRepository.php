<?php

declare(strict_types=1);

namespace App\Repository\Directory;

use App\Entity\Directory\People;
use App\Entity\Directory\PositionClassification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PositionClassificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PositionClassification::class);
    }

    public function getPositionClassificationForPeople(People $people): ?PositionClassification
    {
        $em = $this->getEntityManager();
        $qb = $em->getRepository(PositionClassification::class)->createQueryBuilder('p');
        $qb
            ->innerJoin('p.positions', 'position')
            ->leftJoin('p.businessUnit', 'bu')
            ->where('bu = :businessUnit')
            ->andWhere('position = :position')
            ->setParameter('businessUnit', $people->getBusinessUnit())
            ->setParameter('position', $people->getPosition())
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }
}
