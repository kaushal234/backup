<?php

declare(strict_types=1);

namespace LegacyBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use LegacyBundle\Entity\EquipmentRecord;

class EquipmentRecordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EquipmentRecord::class);
    }

    public function findWarranty(int $equipmentRecordLegacyId): string
    {
        $queryBuilder = $this->createQueryBuilder('s')
            ->select("CASE
                    WHEN s.shippedDate='0000-00-00' OR s.shippedDate IS NULL
                        THEN 'NOT SHIPPED'
                    WHEN DATE_ADD( s.shippedDate, s.warrantyLength, 'month') > NOW() AND s.hours < 2000 AND s.warrantyConditions=''
                        THEN 'EFFECTIVE'
                    WHEN DATE_ADD( s.shippedDate, s.warrantyLength, 'month') > NOW() AND (s.hours BETWEEN 2000 AND 3000 OR (s.hours < 2000 AND s.warrantyConditions!=''))
                        THEN 'MAYBE EXPIRED'
                    ELSE 'EXPIRED'
                END
                AS is_under_warranty")
            ->where('s.id = :id')
            ->setParameter('id', $equipmentRecordLegacyId)
        ;

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }
}
