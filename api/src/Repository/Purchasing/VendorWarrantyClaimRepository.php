<?php

declare(strict_types=1);

namespace App\Repository\Purchasing;

use App\Entity\Purchasing\VendorWarrantyClaim;
use App\Entity\Purchasing\VendorWarrantyClaimStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class VendorWarrantyClaimRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorWarrantyClaim::class);
    }

    public function getVendorToResponsesExpired(): array
    {
        $qb = $this->createQueryBuilder('vwc');

        $qb->join('vwc.factory', 'loc')
            ->join('vwc.status', 'status')
            ->where('status.name = :statusName')
            ->andWhere('vwc.vendorToRespondAt <= :dateLimit')
            ->andWhere('vwc.vendorToRespondAt IS NOT NULL')
            ->andWhere('vwc.requestedCreditAmount != 0')
            ->andWhere('vwc.requestedCreditAmount IS NOT NULL')
            ->andWhere('vwc.currency IS NOT NULL')
            ->setParameter('statusName', VendorWarrantyClaimStatus::VENDOR_TO_RESPOND)
            ->setParameter('dateLimit', new \DateTime('-25 days'))
        ;

        return $qb->getQuery()->getResult();
    }
}
