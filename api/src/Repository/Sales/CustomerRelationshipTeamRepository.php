<?php

declare(strict_types=1);

namespace App\Repository\Sales;

use App\Entity\EquipmentRecord;
use App\Entity\Sales\Customer;
use App\Entity\Sales\CustomerRelationshipTeam;
use App\Entity\Sales\MainSalesRepresentative;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

class CustomerRelationshipTeamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CustomerRelationshipTeam::class);
    }

    public function getIdentifiersForCustomer(Customer $customer): array
    {
        $qb = $this->createQueryBuilder('crt');

        $qb
            ->select('crt.id')
            ->where('crt.customer = :customer')
            ->setParameter('customer', $customer)
        ;

        return $qb->getQuery()->getScalarResult();
    }

    public function findCustomerRelationshipTeamsToUpdate(array $parameters): array
    {
        $qb = $this->createQueryBuilder('crt');
        $qb->where('1 = 1');
        if (isset($parameters['salesRepresentative'])) {
            $qb
                ->andWhere($qb->expr()->eq('crt.salesRepresentative', ':salesRepresentative'))
                ->setParameter('salesRepresentative', $parameters['salesRepresentative'])
            ;
        }

        if (isset($parameters['erpLocation'])) {
            $qb
                ->andWhere($qb->expr()->eq('crt.erpLocation', ':erpLocation'))
                ->setParameter('erpLocation', $parameters['erpLocation'])
            ;
        }

        if (isset($parameters['subDivision'])) {
            $qb
                ->leftJoin(Customer::class, 'c', Join::WITH, 'crt.customer = c.id')
                ->leftJoin(MainSalesRepresentative::class, 'sales_rep', Join::WITH, 'c.mainSalesRepresentative = sales_rep.id')
                ->andWhere($qb->expr()->eq('sales_rep.subDivision', ':subDivision'))
                ->setParameter('subDivision', $parameters['subDivision'])
            ;
        }

        if (isset($parameters['id'])) {
            $qb->andWhere('crt.id = :crtId')
                ->setParameter('crtId', $parameters['id'])
            ;
        }

        return $qb->getQuery()->getResult();
    }

    public function findWithSalesRepresentativeNotSynchronizeWithCustomer()
    {
        return $this->createQueryBuilder('crt')
            ->join('crt.customer', 'c')
            ->join('c.mainSalesRepresentative', 'm')
            ->where('crt.salesRepresentative != m.asm')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findCustomerRelationshipTeamsForEquipmentRecord(EquipmentRecord $equipmentRecord)
    {
        return $this->createQueryBuilder('crt')
            ->join('crt.partsLocation', 'l')
            ->where('crt.customer = :customer')
            ->andWhere('crt.erpLocation = :erpLocation')
            ->andWhere('l.capability.sparePartsHub = :true')
            ->setParameter('customer', $equipmentRecord->getEndUser())
            ->setParameter('erpLocation', $equipmentRecord->getSalesOrganisationService())
            ->setParameter('true', true)
            ->getQuery()
            ->getResult()
        ;
    }
}
