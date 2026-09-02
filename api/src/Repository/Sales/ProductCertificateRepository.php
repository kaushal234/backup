<?php

declare(strict_types=1);

namespace App\Repository\Sales;

use App\Entity\Sales\ProductCertificate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

class ProductCertificateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductCertificate::class);
    }

    /**
     * @return array|ProductCertificate[]
     */
    public function findExpired(): array
    {
        $qb = $this->createQueryBuilder('pc');

        $qb
            ->where('pc.expiredAt < :now')
            ->andWhere('pc.expiredAt >= :yesterday')
            ->setParameters(new ArrayCollection([
                new Parameter('now', (new \DateTime())->format('Y-m-d H:i:s')),
                new Parameter('yesterday', (new \DateTime('yesterday'))->format('Y-m-d H:i:s')),
            ]));

        return $qb->getQuery()->getResult();
    }
}
