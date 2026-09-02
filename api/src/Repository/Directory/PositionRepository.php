<?php

declare(strict_types=1);

namespace App\Repository\Directory;

use App\Entity\Directory\Division;
use App\Entity\Directory\Position;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PositionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Position::class);
    }

    public function getPositionGroupsForDivision(Position $position, Division $division): array
    {
        $em = $this->getEntityManager();
        $qb = $em->getRepository(Position::class)->createQueryBuilder('p');
        $qb
            ->select('g.id')
            ->join('p.divisionGroups', 'div_groups')
            ->join('div_groups.groups', 'g')
            ->join('div_groups.division', 'd')
            ->where('p.id = :positionId')
            ->andWhere('d.id = :divisionId')
            ->setParameter('positionId', $position->getId())
            ->setParameter('divisionId', $division->getId());

        return $qb->getQuery()->getScalarResult();
    }
}
