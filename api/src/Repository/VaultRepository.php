<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Vault;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class VaultRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vault::class);
    }

    public function findOneBySite(int $erp): ?Vault
    {
        return $this->createQueryBuilder('v')
            ->join('v.location', 'l')
            ->where('l.erp = :erp')
            ->setParameter('erp', $erp)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
