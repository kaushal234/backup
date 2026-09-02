<?php

declare(strict_types=1);

namespace App\Repository\Support;

use App\Entity\EquipmentRecord;
use App\Entity\Support\EquipmentFollowUpReport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EquipmentFollowUpReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EquipmentFollowUpReport::class);
    }

    public function getPreviousFollowUpReport(EquipmentRecord $equipmentRecord, \DateTimeInterface $date): ?EquipmentFollowUpReport
    {
        $queryBuilder = $this->createQueryBuilder('f');

        return $queryBuilder
            ->where($queryBuilder->expr()->eq('f.equipmentRecord', ':er'))
            ->andWhere($queryBuilder->expr()->lte('f.hourmeterDate', ':until'))
            ->orderBy('f.hourmeterDate', 'DESC')
            ->addOrderBy('f.createdAt', 'DESC')
            ->setMaxResults(1)
            ->setParameter(':er', $equipmentRecord)
            ->setParameter(':until', $date)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function getNextFollowUpReport(EquipmentRecord $equipmentRecord, \DateTimeInterface $date): ?EquipmentFollowUpReport
    {
        $queryBuilder = $this->createQueryBuilder('f');

        return $queryBuilder
            ->where($queryBuilder->expr()->eq('f.equipmentRecord', ':er'))
            ->andWhere($queryBuilder->expr()->gt('f.hourmeterDate', ':until'))
            ->orderBy('f.hourmeterDate', 'ASC')
            ->addOrderBy('f.createdAt', 'ASC')
            ->setMaxResults(1)
            ->setParameter(':er', $equipmentRecord)
            ->setParameter(':until', $date)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
