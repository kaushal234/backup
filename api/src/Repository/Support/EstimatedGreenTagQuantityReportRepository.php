<?php

declare(strict_types=1);

namespace App\Repository\Support;

use App\Entity\EquipmentRecord;
use App\Entity\Support\EquipmentRecord\EstimatedGreenTagQuantityReport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EstimatedGreenTagQuantityReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EstimatedGreenTagQuantityReport::class);
    }

    public function findByEquipmentRecord(EquipmentRecord $equipmentRecord): array
    {
        return $this->createQueryBuilder('egtqr')
            ->andWhere(':er MEMBER OF egtqr.equipmentRecords')
            ->setParameter('er', $equipmentRecord)
            ->getQuery()->getResult();
    }
}
