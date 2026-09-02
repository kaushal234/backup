<?php

declare(strict_types=1);

namespace App\Repository\Parts;

use App\Entity\Parts\SparePartsRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SparePartsRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SparePartsRequest::class);
    }

    /**
     * @return array<int,SparePartsRequest>
     */
    public function findAndUpdateUnshippedBySalesOrderNumber(string $salesOrderNumber): array
    {
        $qb = $this->createQueryBuilder('spr');

        $qb
            ->where('spr.status NOT IN (:statuses)')
            ->andWhere('spr.salesOrder = :salesOrder')
            ->setParameter('statuses', [SparePartsRequest::STATUS_SHIPPED, SparePartsRequest::STATUS_CLOSED])
            ->setParameter('salesOrder', $salesOrderNumber)
        ;

        $sparePartsRequests = $qb->getQuery()->getResult();

        $updateQb = $this->createQueryBuilder('spr');

        $updateQb
            ->update()
            ->set('spr.status', ':shipped')
            ->where('spr.status NOT IN (:statuses)')
            ->andWhere('spr.salesOrder = :salesOrder')
            ->setParameter('statuses', [SparePartsRequest::STATUS_SHIPPED, SparePartsRequest::STATUS_CLOSED])
            ->setParameter('salesOrder', $salesOrderNumber)
            ->setParameter('shipped', SparePartsRequest::STATUS_SHIPPED)
        ;

        $updateQb->getQuery()->execute();

        return $sparePartsRequests;
    }
}
