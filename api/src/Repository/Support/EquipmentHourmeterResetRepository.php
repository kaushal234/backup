<?php

declare(strict_types=1);

namespace App\Repository\Support;

use App\Entity\EquipmentRecord;
use App\Entity\Support\EquipmentHourmeterReset;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EquipmentHourmeterResetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EquipmentHourmeterReset::class);
    }

    public function getResetHoursSum(EquipmentRecord $equipmentRecord, \DateTime $until): int
    {
        $queryBuilder = $this->createQueryBuilder('r');

        return (int) $queryBuilder
            ->select('SUM(r.hourmeter) AS hourmeter')
            ->where($queryBuilder->expr()->eq('r.equipmentRecord', ':er'))
            ->andWhere($queryBuilder->expr()->lte('r.hourmeterDate', ':until'))
            ->setParameter(':er', $equipmentRecord)
            ->setParameter(':until', $until)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
