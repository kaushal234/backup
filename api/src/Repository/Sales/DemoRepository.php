<?php

declare(strict_types=1);

namespace App\Repository\Sales;

use App\Entity\Sales\Customer;
use App\Entity\Sales\Demo;
use App\Entity\Sales\Product;
use App\Entity\Sales\ProductFamily;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

class DemoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Demo::class);
    }

    /**
     * @return array|Demo[]
     */
    public function findExpiredDemos(): array
    {
        $qb = $this->createQueryBuilder('q');

        $qb
            ->where('q.status IN (:statuses)')
            ->andWhere('q.delinquent = :false')
            ->andWhere($qb->expr()->orX(
                'COALESCE(q.revisedEndDate, q.expectedEndDate) < :now',
                'q.lastCommentedAt < :one_month_ago AND q.status = :active AND q.activatedAt < :one_month_ago',
                'q.lastCommentedAt IS NULL',
                'q.lastCommentedAt < :tree_month_ago AND q.status != :active',
            ))
            ->setParameters(new ArrayCollection([
                new Parameter('statuses', Demo::OPEN_STATUSES),
                new Parameter('false', false),
                new Parameter('now', new \DateTime('today')),
                new Parameter('one_month_ago', new \DateTime('1 month ago')),
                new Parameter('active', Demo::ACTIVE),
                new Parameter('tree_month_ago', new \DateTime('3 month ago')),
            ]));

        return $qb->getQuery()->getResult();
    }

    public function getIdentifiersForCustomer(Customer $customer): array
    {
        $qb = $this->createQueryBuilder('d');

        $qb
            ->select('d.id')
            ->where('d.customer = :customer')
            ->setParameter('customer', $customer)
        ;

        return $qb->getQuery()->getScalarResult();
    }

    public function getIdentifiersForProduct(Product $product): array
    {
        $qb = $this->createQueryBuilder('d');

        $qb
            ->select('d.id')
            ->where('d.product = :product')
            ->setParameter('product', $product);

        return $qb->getQuery()->getScalarResult();
    }

    public function getIdentifiersForProductFamily(ProductFamily $productFamily): array
    {
        $qb = $this->createQueryBuilder('d');

        $qb
            ->select('d.id')
            ->join('d.product', 'p')
            ->where('p.family = :productFamily')
            ->setParameter('productFamily', $productFamily);

        return $qb->getQuery()->getScalarResult();
    }

    public function findDemoReadyToBeActive()
    {
        $qb = $this->createQueryBuilder('d');

        $qb
            ->join('d.equipmentRecord', 'e')
            ->where('d.approvalDate < e.dateCommissioned')
            ->andWhere('d.status = :status')
            ->setParameter('status', Demo::APPROVED)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findUncommentedDemo()
    {
        $qb = $this->createQueryBuilder('d');

        $qb
            ->where('DATE_DIFF(NOW(), d.lastCommentedAt)=25')
            ->andWhere('d.status IN (:statuses)')
            ->setParameter('statuses', Demo::OPEN_STATUSES)
        ;

        return $qb->getQuery()->getResult();
    }
}
