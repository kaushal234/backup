<?php

declare(strict_types=1);

namespace App\Repository\Parts;

use App\Entity\Parts\TOCSparePartsRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TOCSparePartsRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TOCSparePartsRequest::class);
    }

    public function findSparePartsRequestForToc(int $tocId): ?TOCSparePartsRequest
    {
        $queryBuilder = $this->createQueryBuilder('spr');

        $sparePartsRequests = $queryBuilder
            ->where('spr.tocId = :tocId')
            ->andWhere($queryBuilder->expr()->in('spr.status', ':statuses'))
            ->andWhere('spr.salesOrder IS NOT NULL')
            ->setParameter('tocId', $tocId)
            ->setParameter('statuses', ['PENDING', 'OPEN'])
            ->getQuery()
            ->getResult()
        ;

        return \count($sparePartsRequests) > 0 ? $sparePartsRequests[0] : null;
    }
}
