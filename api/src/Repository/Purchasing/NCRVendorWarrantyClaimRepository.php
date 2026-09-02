<?php

declare(strict_types=1);

namespace App\Repository\Purchasing;

use App\Entity\Purchasing\NCRVendorWarrantyClaim;
use App\Entity\Quality\NonConformity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NCRVendorWarrantyClaimRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NCRVendorWarrantyClaim::class);
    }

    public function getIdentifiersForNonConformity(NonConformity $nonConformity): array
    {
        $qb = $this->createQueryBuilder('vwc');

        $qb
            ->select('vwc.id')
            ->where('vwc.nonConformity = :non_conformity')
            ->setParameter('non_conformity', $nonConformity)
        ;

        return $qb->getQuery()->getScalarResult();
    }
}
