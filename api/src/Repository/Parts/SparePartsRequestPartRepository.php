<?php

declare(strict_types=1);

namespace App\Repository\Parts;

use App\Entity\Parts\SparePartsRequest;
use App\Entity\Parts\SparePartsRequestPart;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SparePartsRequestPartRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SparePartsRequestPart::class);
    }

    public function getPartsIds(SparePartsRequest $sparePartsRequest): array
    {
        $qb = $this->createQueryBuilder('p');

        $qb
            ->select('p.partNumber')
            ->addSelect('p.deletedAt')
            ->where('p.sparePartsRequest = :sparePartsRequest')
            ->setParameter('sparePartsRequest', $sparePartsRequest)
        ;

        return $qb->getQuery()->getScalarResult();
    }
}
