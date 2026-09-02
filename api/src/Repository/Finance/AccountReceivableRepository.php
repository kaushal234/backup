<?php

declare(strict_types=1);

namespace App\Repository\Finance;

use App\Entity\Directory\Location;
use App\Entity\Finance\AccountReceivable;
use App\Entity\Finance\Currency;
use App\Entity\Finance\ExchangeRate;
use App\Entity\Finance\InvoiceRecord;
use App\Entity\Sales\Customer;
use App\Entity\Sales\CustomerErpReference;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

class AccountReceivableRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccountReceivable::class);
    }

    public function removeForErp(Location $location)
    {
        $qb = $this->createQueryBuilder('ar');
        $ids = $qb
            ->leftJoin(CustomerErpReference::class, 'cus_erp', Join::WITH, 'cus_erp = ar.customerErpReference')
            ->leftJoin(Location::class, 'l', Join::WITH, 'l.id = cus_erp.sso')
            ->where($qb->expr()->eq('l.id', ':sso_id'))
            ->setParameter('sso_id', $location->getId())
            ->getQuery()
            ->getResult()
        ;

        $qb = $this->createQueryBuilder('ar');
        $qb
            ->delete()
            ->where($qb->expr()->in('ar.id', ':ids'))
            ->setParameter('ids', $ids)
            ->getQuery()->execute()
        ;
    }

    public function findDelinquents(?Location $location = null)
    {
        $qb = $this->createQueryBuilder('ar');

        /** @var ExchangeRateRepository $exchangeRateRepository */
        $exchangeRateRepository = $this->getEntityManager()->getRepository(ExchangeRate::class);
        $condition = $exchangeRateRepository->getIfConditionForExchangeRates('c');

        $orStatements = $qb->expr()->orX();

        $orStatements
            ->add($qb->expr()->andX(
                $qb->expr()->gt(\sprintf('ar.balanceAmount / %s', $condition), ':hundred_thousands'),
                $qb->expr()->lt('COALESCE(i.revisedDueDate, ar.dueDate)', ':today')))
            ->add($qb->expr()->andX(
                $qb->expr()->gt(\sprintf('ar.balanceAmount / %s', $condition), ':twenty_thousands'),
                $qb->expr()->lt('COALESCE(i.revisedDueDate, ar.dueDate)', ':sixty_days_ago')
            ));

        $qb
            ->leftJoin(CustomerErpReference::class, 'cus_erp', Join::WITH, 'cus_erp.id = ar.customerErpReference')
            ->leftJoin(InvoiceRecord::class, 'i', Join::WITH, 'cus_erp.id = i.customerErpReference AND ar.erpInvoiceNumber = i.invoiceNumber')
            ->leftJoin(Currency::class, 'c', Join::WITH, 'ar.currency = c.id')
            ->andWhere($orStatements)
            ->setParameters(new ArrayCollection([
                new Parameter('hundred_thousands', AccountReceivable::DELINQUENT_MINIMUM),
                new Parameter('twenty_thousands', AccountReceivable::DELINQUENT_MINIMUM_WITH_PAST_DUE),
                new Parameter('sixty_days_ago', new \DateTime('60 days ago')),
                new Parameter('today', new \DateTime()),
            ]))
        ;

        if (null !== $location) {
            $qb->andWhere($qb->expr()->eq('cus_erp.sso', ':location'))->setParameter('location', $location->getId());
        }

        return $qb->getQuery()->getResult();
    }

    public function getCustomerIdentifiersForAccountReceivables(Customer $customer): array
    {
        $qb = $this->createQueryBuilder('ar');
        $qb
            ->select('ar.id')
            ->leftJoin(CustomerErpReference::class, 'cus_erp', Join::WITH, 'cus_erp.id = ar.customerErpReference')
            ->where($qb->expr()->eq('cus_erp.customer', ':customer'))
            ->setParameter('customer', $customer)
        ;

        return $qb->getQuery()->getScalarResult();
    }

    public function getCustomerErpReferenceIdentifiersForAccountReceivables(CustomerErpReference $customerErpReference): array
    {
        $qb = $this->createQueryBuilder('ar');
        $qb
            ->select('ar.id')
            ->where($qb->expr()->eq('ar.customerErpReference', ':customerErpReference'))
            ->setParameter('customerErpReference', $customerErpReference)
        ;

        return $qb->getQuery()->getScalarResult();
    }
}
