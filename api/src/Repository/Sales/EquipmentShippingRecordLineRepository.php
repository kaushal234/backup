<?php

declare(strict_types=1);

namespace App\Repository\Sales;

use App\Entity\Directory\Location;
use App\Entity\EquipmentRecord;
use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecordLine;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

class EquipmentShippingRecordLineRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
    ) {
        parent::__construct($registry, EquipmentShippingRecordLine::class);
    }

    public function findLastEquipmentShippingRecordLineByEquipmentRecord(EquipmentRecord $equipmentRecord): ?EquipmentShippingRecordLine
    {
        $qb = $this->createQueryBuilder('l');

        $result = $qb
            ->where($qb->expr()->eq('l.equipmentRecord', ':er'))
            ->orderBy('l.id', 'DESC')
            ->setMaxResults(1)
            ->setParameter('er', $equipmentRecord)
            ->getQuery()
            ->getResult()
        ;

        return $result[0] ?? null;
    }

    public function countEquipmentShippingRecordLineWithSameFactoryAndEstimatedPickUpDate(int $factoryId, string $estimatedPickUpDate): array
    {
        $qb = $this->createQueryBuilder('l');

        $qb
            ->select('COUNT(DISTINCT l.id) AS count')
            ->leftJoin(EquipmentRecord::class, 'er', Join::WITH, 'l.equipmentRecord = er')
            ->leftJoin(Location::class, 'factory', Join::WITH, 'er.manufacturerLocation = factory')
            ->where($qb->expr()->eq('factory.id', ':factoryId'))
            ->andWhere('l.estimatedPickUpDate >= :startOfDay')
            ->andWhere('l.estimatedPickUpDate <= :endOfDay')
            ->setParameters(new ArrayCollection([
                new Parameter('factoryId', $factoryId),
                new Parameter('startOfDay', (new \DateTime($estimatedPickUpDate))->setTime(0, 0, 0)),
                new Parameter('endOfDay', (new \DateTime($estimatedPickUpDate))->setTime(23, 59, 59)),
            ]))
        ;

        return $qb->getQuery()->getResult();
    }
}
