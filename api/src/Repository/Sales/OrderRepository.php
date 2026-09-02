<?php

declare(strict_types=1);

namespace App\Repository\Sales;

use App\Entity\Directory\Location;
use App\Entity\Sales\Customer;
use App\Entity\Sales\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /**
     * @return array|Order[]
     */
    public function findPendingOrders(?\DateTimeInterface $since = null): array
    {
        $qb = $this->createQueryBuilder('o');

        $qb
            ->addSelect(['p', 'sso', 'jl', 'b', 'eu'])
            ->join('o.asm', 'p')
            ->join('o.sso', 'sso')
            ->join('o.juridicalLocation', 'jl')
            ->leftjoin('o.buyer', 'b')
            ->leftjoin('o.endUser', 'eu')
            ->where('o.status = :status')
            ->setParameter('status', Order::PENDING)
        ;

        if (null !== $since) {
            $qb
                ->andWhere('o.enteredAt >= :since')
                ->setParameter('since', $since)
            ;
        }

        return $qb->getQuery()->getResult();
    }

    public function getIdentifiersForCustomer(Customer $customer): array
    {
        $qb = $this->createQueryBuilder('o');

        $qb
            ->select('o.id')
            ->orWhere('o.buyer = :customer')
            ->orWhere('o.endUser = :customer')
            ->orWhere('o.salesAgent = :customer')
        ;

        $qb->setParameter('customer', $customer);

        return $qb->getQuery()->getScalarResult();
    }

    public function findOrderForAccountReceivable(Location $location, array $accountReceivable): ?Order
    {
        $qb = $this->createQueryBuilder('o');

        $qb
            ->where($qb->expr()->eq('o.sso', ':location_id'))
            ->andWhere($qb->expr()->eq('o.baanCustomerNumber', ':customer_number'))
            ->andWhere($qb->expr()->like('o.baanOrderNumbers', ':baan_order_number'))
            ->setParameters(new ArrayCollection([
                new Parameter('location_id', $location->getId()),
                new Parameter('customer_number', $accountReceivable['pcust']),
                new Parameter('baan_order_number', '%'.$accountReceivable['salesOrderNumber'].'%'),
            ]))
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }
}
