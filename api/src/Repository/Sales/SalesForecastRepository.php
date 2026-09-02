<?php

declare(strict_types=1);

namespace App\Repository\Sales;

use App\Entity\Directory\Location;
use App\Entity\Sales\Customer;
use App\Entity\Sales\Product;
use App\Entity\Sales\ProductFamily;
use App\Entity\Sales\SalesForecast;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

class SalesForecastRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SalesForecast::class);
    }

    public function getOriginalFactoryId(SalesForecast $salesForecast)
    {
        $qb = $this->createQueryBuilder('s');

        $qb
            ->select('l.id AS factory')
            ->join(Location::class, 'l', Join::WITH, 's.factory = l.id')
            ->where('s.id = :sfrId')
        ;

        $qb->setParameter('sfrId', $salesForecast->getId());

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return SalesForecast[]
     */
    public function findNewDelinquent(): array
    {
        $qb = $this->createQueryBuilder('s');

        $orStatements = $qb->expr()->orX();

        $orStatements->add($qb->expr()->andX(
            "PERIOD_DIFF(DATE_FORMAT(s.estimatedSaleDate, '%Y%m'), DATE_FORMAT(NOW(), '%Y%m')) < 3",
            's.updatedAt < :one_month_ago'
        ));

        $orStatements->add($qb->expr()->andX(
            "PERIOD_DIFF(DATE_FORMAT(s.estimatedSaleDate, '%Y%m'), DATE_FORMAT(NOW(), '%Y%m')) >= 3",
            "PERIOD_DIFF(DATE_FORMAT(s.estimatedSaleDate, '%Y%m'), DATE_FORMAT(NOW(), '%Y%m')) < 6",
            's.updatedAt < :two_months_ago'
        ));

        $orStatements->add($qb->expr()->andX(
            "PERIOD_DIFF(DATE_FORMAT(s.estimatedSaleDate, '%Y%m'), DATE_FORMAT(NOW(), '%Y%m')) >= 6",
            's.updatedAt < :three_months_ago'
        ));

        $orStatements->add($qb->expr()->andX(
            "s.estimatedSaleDate < DATE_FORMAT(NOW(), '%Y-%m-%d')"
        ));

        $qb->setParameter('one_month_ago', new \DateTimeImmutable('1 month ago'));
        $qb->setParameter('two_months_ago', new \DateTimeImmutable('2 months ago'));
        $qb->setParameter('three_months_ago', new \DateTimeImmutable('3 months ago'));

        $qb->andWhere($orStatements);

        $qb->andWhere('s.status IN (:statuses)')->setParameter('statuses', SalesForecast::OPEN_STATUSES);
        $qb->andWhere('s.delinquent = :delinquent')->setParameter('delinquent', false);

        return $qb->getQuery()->getResult();
    }

    /**
     * @return SalesForecast[]
     */
    public function findOpenAndDeliquent(): array
    {
        $qb = $this->createQueryBuilder('s');

        $qb->andWhere('s.status IN (:statuses)')->setParameter('statuses', SalesForecast::OPEN_STATUSES);
        $qb->andWhere('s.delinquent = :delinquent')->setParameter('delinquent', true);

        return $qb->getQuery()->getResult();
    }

    /**
     * @return SalesForecast[]
     */
    public function findOpen(): array
    {
        $qb = $this->createQueryBuilder('s');

        $qb->andWhere('s.status IN (:statuses)')->setParameter('statuses', SalesForecast::OPEN_STATUSES);

        return $qb->getQuery()->getResult();
    }

    /**
     * @return SalesForecast[]
     */
    public function findClosedAndNotNotified(\DateTime $before, \DateTime $after): array
    {
        $qb = $this->createQueryBuilder('s');

        $qb
            ->andWhere('s.status IN (:statuses)')
            ->andWhere('s.closureNotificationSentAt IS NULL')
            ->andWhere('s.closedAt <= :before')
            ->andWhere('s.closedAt > :after')
        ;

        $qb->setParameters(new ArrayCollection([
            new Parameter('before', $before),
            new Parameter('after', $after),
            new Parameter('statuses', [SalesForecast::ORDERED, SalesForecast::LOST, SalesForecast::PARTIAL, SalesForecast::ORDER_CANCELLED]),
        ]));

        return $qb->getQuery()->getResult();
    }

    public function getIdentifiersForCustomer(Customer $customer): array
    {
        $qb = $this->createQueryBuilder('s');

        $qb
            ->select('s.id')
            ->orWhere('s.endUser = :customer')
            ->orWhere('s.buyer = :customer')
            ->orWhere('s.thirdParty = :customer')
        ;

        $qb->setParameter('customer', $customer);

        return $qb->getQuery()->getScalarResult();
    }

    public function getIdentifiersForProduct(Product $product): array
    {
        $qb = $this->createQueryBuilder('s');

        $qb
            ->select('s.id')
            ->Where('s.product = :product')
            ->setParameter('product', $product);

        return $qb->getQuery()->getScalarResult();
    }

    public function getIdentifiersForProductFamily(ProductFamily $productFamily): array
    {
        $qb = $this->createQueryBuilder('s');

        $qb
            ->select('s.id')
            ->join('s.product', 'p')
            ->Where('p.family = :productFamily')
            ->setParameter('productFamily', $productFamily);

        return $qb->getQuery()->getScalarResult();
    }

    public function findHotAndOpenByProductFamilyAndFactory(ProductFamily $productFamily, Location $factory): array
    {
        $qb = $this->createQueryBuilder('s');

        $qb
            ->join('s.product', 'p')
            ->where('p.family = :productFamily')
            ->andWhere('s.status IN (:statuses)')
            ->andWhere('s.factory = :factory')
            ->andWhere('s.customerSuccessPercentage > (:percentage)')
            ->andWhere($qb->expr()->between(
                's.estimatedSaleDate',
                ':startDate',
                ':endDate'
            ))
            ->setParameters(new ArrayCollection([
                new Parameter('productFamily', $productFamily),
                new Parameter('factory', $factory),
                new Parameter('statuses', SalesForecast::OPEN_STATUSES),
                new Parameter('percentage', SalesForecast::HOT_DEALS_PERCENTAGE),
                new Parameter('startDate', new \DateTime()),
                new Parameter('endDate', new \DateTime('+ 2 months')),
            ]));

        return $qb->getQuery()->getResult();
    }
}
