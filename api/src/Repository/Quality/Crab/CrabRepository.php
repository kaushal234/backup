<?php

declare(strict_types=1);

namespace App\Repository\Quality\Crab;

use App\Entity\Directory\Location;
use App\Entity\EquipmentRecord;
use App\Entity\Quality\Crab;
use App\Entity\Quality\CrabCode;
use App\Entity\Quality\NonConformity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class CrabRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Crab::class);
    }

    public function findOpenCrabByEquipmentRecord(EquipmentRecord $equipmentRecord)
    {
        $qb = $this->createQueryBuilder('q');

        return $qb
            ->where('q.status NOT IN (:closed)')
            ->andWhere($qb->expr()->eq('q.equipmentRecord', ':er'))
            ->setParameter('closed', Crab::CLOSED)
            ->setParameter('er', $equipmentRecord)
            ->getQuery()
            ->getResult()
        ;
    }

    public function countCrabByCrabCodeByPeriod(\DateTimeInterface $from, \DateTimeInterface $to, Location $factory): QueryBuilder
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        return $queryBuilder
            ->select('COUNT(crab) AS value')
            ->addSelect('cc.description AS x')
            ->addSelect('crab.category AS y')
            ->from(Crab::class, 'crab')
            ->innerJoin(CrabCode::class, 'cc', Join::WITH, 'crab.code = cc')
            ->leftJoin(EquipmentRecord::class, 'er', Join::WITH, 'crab.equipmentRecord = er')
            ->leftJoin(Location::class, 'l', Join::WITH, 'er.manufacturerLocation = l')
            ->where($queryBuilder->expr()->gt('crab.createdAt', ':createdAfter'))
            ->andWhere($queryBuilder->expr()->lt('crab.createdAt', ':createdBefore'))
            ->andWhere($queryBuilder->expr()->eq('l', ':factory'))
            ->groupBy('cc, crab.category')
            ->orderBy('value', Criteria::DESC)
            ->setParameter('createdAfter', $from->format('Y-m-d'))
            ->setParameter('createdBefore', $to->format('Y-m-d'))
            ->setParameter('factory', $factory)
        ;
    }

    public function getIdentifiersForNonConformity(NonConformity $nonConformity)
    {
        $qb = $this->createQueryBuilder('crab');

        $qb->select('crab.id')
            ->where('crab.nonConformity = :non_conformity')
            ->setParameter('non_conformity', $nonConformity)
        ;

        return $qb->getQuery()->getScalarResult();
    }

    public function findCrabsWithoutPiQuestionParentId(): array
    {
        $qb = $this->createQueryBuilder('crab');

        return $qb
            ->where('crab.piQuestionId IS NOT NULL')
            ->andWhere($qb->expr()->neq('crab.piQuestionId', 0))
            ->andWhere('crab.piQuestionParentId IS NULL')
            ->getQuery()
            ->getResult()
        ;
    }
}
