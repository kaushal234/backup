<?php

declare(strict_types=1);

namespace LegacyBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use LegacyBundle\Entity\WarrantyClaim;

/**
 * @extends ServiceEntityRepository<WarrantyClaim>
 */
class WarrantyClaimRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WarrantyClaim::class);
    }

    /**
     * @param int[] $ids
     *
     * @return int[]
     */
    public function findNonPendingIds(array $ids): array
    {
        $qb = $this->createQueryBuilder('wc');
        $nonPendingWc = $qb
            ->select('wc.id')
            ->where($qb->expr()->in('wc.id', ':ids'))
            ->andWhere($qb->expr()->neq('wc.status', ':status'))
            ->setParameter('ids', $ids)
            ->setParameter('status', WarrantyClaim::PENDING)
            ->getQuery()
            ->getResult();

        return array_column($nonPendingWc, 'id');
    }
}
