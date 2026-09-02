<?php

declare(strict_types=1);

namespace App\Repository\Parts;

use App\Entity\Parts\SparePartsRequest;
use App\Entity\Parts\SparePartsRequestDeliveryAddress;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

class SparePartsDeliveryAddressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SparePartsRequestDeliveryAddress::class);
    }

    public function getUnusedDeliveryAddresses(): array
    {
        $qb = $this->createQueryBuilder('d');

        $qb
            ->leftJoin(SparePartsRequest::class, 'spr', Join::WITH, 'spr.deliveryAddress = d.id')
            ->where($qb->expr()->isNull('spr.id'))
            ->andWhere($qb->expr()->lt('d.createdAt', ':one_month_ago'))
            ->setParameter('one_month_ago', (new \DateTime('1 month ago'))->format('Y-m-d'))
        ;

        return $qb->getQuery()->getResult();
    }
}
