<?php

declare(strict_types=1);

namespace LegacyBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use LegacyBundle\Entity\ServiceBulletin;

class ServiceBulletinRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ServiceBulletin::class);
    }

    public function findCompulsoryByEquipmentRecordId(int $equipmentRecordLegacyId)
    {
        return $this->createQueryBuilder('s')
            ->join('s.lines', 'l')
            ->where('l.equipmentRecord = :equipmentRecordLegacyId')
            ->andWhere('s.category = :category')
            ->andWhere('s.status LIKE :status')
            ->andWhere('l.status != :line_status')
            ->setParameter('equipmentRecordLegacyId', $equipmentRecordLegacyId)
            ->setParameter('category', 'COMPULSORY')
            ->setParameter('status', '%IMPLEMENTATION')
            ->setParameter('line_status', 'CLOSED')
            ->getQuery()
            ->getResult()
        ;
    }
}
