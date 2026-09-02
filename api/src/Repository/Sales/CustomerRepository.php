<?php

declare(strict_types=1);

namespace App\Repository\Sales;

use App\Entity\Country;
use App\Entity\Directory\People;
use App\Entity\Directory\SubDivision;
use App\Entity\Sales\Customer;
use App\Entity\Sales\CustomerErpReference;
use App\Entity\Sales\CustomerType;
use App\Entity\Sales\MainSalesRepresentative;
use App\Entity\Sales\Order;
use App\Entity\Sales\SalesForecast;
use App\Entity\Sales\SecondarySalesRepresentative;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

class CustomerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customer::class);
    }

    public function hideCustomer(Customer $customer)
    {
        $this->getEntityManager()->refresh($customer);

        $customer->setHidden(true);

        $this->getEntityManager()->persist($customer);
        $this->getEntityManager()->flush();
    }

    public function deleteCustomer(Customer $customer)
    {
        $this->getEntityManager()->remove($customer);

        $this->getEntityManager()->flush();
    }

    public function findCustomersByType(CustomerType $customerType): Collection
    {
        $qb = $this->createQueryBuilder('c');
        $qb
            ->join('c.customerTypes', 'type')
            ->where($qb->expr()->eq('type.id', ':id'))
            ->setParameter('id', $customerType->getId())
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * This method find all customers that need to be back to re-approval status.
     *
     * To be in re-approval status, customer should have a validateAt field to null OR validateAt less than 3 years old.
     * OR less than 5 years old if the customer has some activites in Sales Forecast Record module and Sales Orders
     * Records module. Also, these activities depends on the customer type, he should NOT part of these types:
     * CustomerType::THIRD_PARTIES_NAMES
     *
     * And last condition, customer should already have APPROVED status and should NOT be hidden.
     */
    public function findCustomersToBeReApproved(): array
    {
        $qb = $this->createQueryBuilder('c');

        $subQuerySFR = $this->createQueryBuilder('sfr');
        $orStatementsSubQuerySFR = $subQuerySFR->expr()->orX();
        $orStatementsSubQuerySFR->add($subQuerySFR->expr()->eq('sfr.endUser', 'c.id'));
        $orStatementsSubQuerySFR->add($subQuerySFR->expr()->eq('sfr.buyer', 'c.id'));
        $orStatementsSubQuerySFR->add($subQuerySFR->expr()->eq('sfr.thirdParty', 'c.id'));
        $subQuerySFR
            ->resetDQLPart('from')
            ->select($subQuerySFR->expr()->count('sfr'))
            ->from(SalesForecast::class, 'sfr')
            ->where($subQuerySFR->expr()->gt('sfr.createdAt', ':three_years_ago'))
            ->andWhere($subQuerySFR->expr()->notIn('ct.name', ':agent_distributors'))
            ->andWhere($orStatementsSubQuerySFR)
        ;

        $subQuerySOR = $this->createQueryBuilder('sor');
        $orStatementsSubQuerySOR = $subQuerySOR->expr()->orX();
        $orStatementsSubQuerySOR->add($subQuerySOR->expr()->eq('sor.endUser', 'c.id'));
        $orStatementsSubQuerySOR->add($subQuerySOR->expr()->eq('sor.buyer', 'c.id'));
        $orStatementsSubQuerySOR->add($subQuerySOR->expr()->eq('sor.salesAgent', 'c.id'));
        $subQuerySOR
            ->resetDQLPart('from')
            ->select($subQuerySOR->expr()->count('sor'))
            ->from(Order::class, 'sor')
            ->where($subQuerySOR->expr()->gt('sor.enteredAt', ':three_years_ago'))
            ->andWhere($subQuerySOR->expr()->notIn('ct.name', ':agent_distributors'))
            ->andWhere($orStatementsSubQuerySOR)
        ;

        $orStatements = $qb->expr()->orX();
        $orStatements->add($qb->expr()->isNull('c.validatedAt'));
        $orStatements->add($qb->expr()->lt('c.validatedAt', \sprintf('CASE WHEN (%s) > 0 OR (%s) > 0 THEN :five_years_ago ELSE :three_years_ago END', $subQuerySFR->getQuery()->getDQL(), $subQuerySOR->getQuery()->getDQL())));

        $qb
            ->leftJoin('c.customerTypes', 'ct')
            ->where($orStatements)
            ->andWhere($qb->expr()->eq('c.status', ':status'))
            ->andWhere($qb->expr()->eq('c.hidden', ':hidden'))
            ->setMaxResults(100)
            ->setParameter('three_years_ago', new \DateTime('3 years ago'))
            ->setParameter('five_years_ago', new \DateTime('5 years ago'))
            ->setParameter('status', Customer::APPROVED)
            ->setParameter('hidden', false)
            ->setParameter('agent_distributors', CustomerType::THIRD_PARTIES_NAMES)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findCustomersReApprovedMoreThanSixMonthsAgo(): array
    {
        $qb = $this->createQueryBuilder('c');

        $qb
            ->where($qb->expr()->lt('c.reApprovedAt', ':six_months_ago'))
            ->andWhere($qb->expr()->isNotNull('c.reApprovedAt'))
            ->andWhere($qb->expr()->eq('c.status', ':status'))
            ->andWhere($qb->expr()->eq('c.hidden', ':hidden'))
            ->setParameter('six_months_ago', new \DateTime('6 months ago'))
            ->setParameter('status', Customer::PENDING_RE_APPROVAL)
            ->setParameter('hidden', false)
        ;

        return $qb->getQuery()->getResult();
    }

    public function changeCustomerMainRepresentative(array $customers, People $asm)
    {
        /** @var Customer $customer */
        foreach ($customers as $customer) {
            $this->changeMainRepresentative($customer, $asm);
            $crts = $customer->getCrt();

            foreach ($crts as $crt) {
                $crt->setSalesRepresentative($asm);
                $this->getEntityManager()->persist($crt);
            }
        }
        $this->getEntityManager()->flush();
    }

    public function findCustomersOnWatchListwithoutERPReference()
    {
        $qb = $this->createQueryBuilder('c');

        $qb
            ->leftJoin(CustomerErpReference::class, 'cus_erp', Join::WITH, 'c.id = cus_erp.customer')
            ->where($qb->expr()->isNull('c.deletedAt'))
            ->andWhere($qb->expr()->isNull('cus_erp.id'))
            ->andWhere($qb->expr()->eq('c.watchList', ':watch_list'))
            ->setParameter('watch_list', true)
        ;

        return $qb->getQuery()->getResult();
    }

    public function getActualSecondarySalesRepresentatives(Customer $customer): array
    {
        $qb = $this->createQueryBuilder('c');

        $qb
            ->select('asm.id as asm_id')
            ->addSelect('asm.lastname')
            ->addSelect('asm.firstname')
            ->addSelect('sub_div.id as subDivision_id')
            ->addSelect('sub_div.name as subDivision_name')
            ->leftJoin(SecondarySalesRepresentative::class, 'sales_rep', Join::WITH, 'c.id = sales_rep.customer')
            ->leftJoin(People::class, 'asm', Join::WITH, 'asm.id = sales_rep.asm')
            ->leftJoin(SubDivision::class, 'sub_div', Join::WITH, 'sub_div.id = sales_rep.subDivision')
            ->where($qb->expr()->eq('c.id', ':id'))
            ->setParameter('id', $customer->getId())
        ;

        return $qb->getQuery()->getScalarResult();
    }

    public function getCustomersByMainRepresentativeCountry(People $asm, ?Country $country = null): array
    {
        $qb = $this->createQueryBuilder('c');
        $qb
            ->leftJoin(MainSalesRepresentative::class, 'sales_rep', Join::WITH, 'c.mainSalesRepresentative = sales_rep.id')
            ->leftJoin(People::class, 'asm', Join::WITH, 'asm.id = sales_rep.asm')
            ->where($qb->expr()->eq('asm.id', ':asm'))
            ->setParameter('asm', $asm->getId())
        ;

        if (null !== $country) {
            $qb
                ->andWhere($qb->expr()->eq('c.country', ':country'))
                ->setParameter('country', $country->getId())
            ;
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return iterable<Customer>
     */
    public function walkCustomerHierarchy(Customer $customer): iterable
    {
        $i = 1;

        do {
            yield $customer;

            $customer = $customer->getParentCustomer();
            if (null === $customer) {
                break;
            }

            try {
                // this is here to prevent a soft deleted customer to be loaded here
                // and throw an EntityNotFoundException in the next iteration of the loop
                $this->getEntityManager()->initializeObject($customer);
            } catch (EntityNotFoundException) {
                break;
            }

            ++$i;
        } while ($i <= 10);
    }

    private function changeMainRepresentative(Customer $customer, People $asm)
    {
        $mainSalesRepresentative = $customer->getMainSalesRepresentative();
        $mainSalesRepresentative->asm = $asm;
        $mainSalesRepresentative->subDivision = $asm->getBusinessUnit()->getRegion()->getSubDivision();

        $customer->setMainSalesRepresentative($mainSalesRepresentative);
        $this->getEntityManager()->persist($customer);
    }
}
