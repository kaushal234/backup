<?php

declare(strict_types=1);

namespace App\Repository\Sales;

use App\Entity\Sales\Customer;
use App\Entity\Sales\CustomerRelationshipTeam;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Sales\ExtranetUserAcl;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ExtranetUserAclRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExtranetUserAcl::class);
    }

    public function loadRolesByExtranetUser(User $user)
    {
        return $this->createQueryBuilder('r')
            ->select('g.name AS role')
            ->distinct(true)
            ->leftJoin('r.extranetUserGroup', 'g')
            ->where('r.extranetUser = :extranetUser')
            ->setParameter(':extranetUser', $user)
            ->getQuery()
            ->getResult();
    }

    public function findCustomerRelationTeamForCustomer(ExtranetUser $user, Customer $customer)
    {
        return $this->createQueryBuilder('o')
            ->leftJoin('o.crt', 'c')
            ->where('o.extranetUser = :extranetUser')
            ->andWhere('c.customer = :customer')
            ->setParameter('extranetUser', $user)
            ->setParameter('customer', $customer)
            ->getQuery()
            ->getResult()
        ;
    }

    public function userHasGroup(ExtranetUser $user, string $groupName): bool
    {
        return null !== $this->createQueryBuilder('o')
            ->select('o.id')
            ->leftJoin('o.extranetUserGroup', 'g')
            ->where('o.extranetUser = :user')
            ->andWhere('g.name = :groupName')
            ->setParameter('user', $user)
            ->setParameter('groupName', $groupName)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function getIdentifiersForCustomerRelationshipTeam(CustomerRelationshipTeam $customerRelationshipTeam): array
    {
        $qb = $this->createQueryBuilder('xa');

        $qb
            ->select('xa.id')
            ->where('xa.crt = :customerRelationshipTeam')
            ->setParameter('customerRelationshipTeam', $customerRelationshipTeam)
        ;

        return $qb->getQuery()->getScalarResult();
    }
}
