<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Acl;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Group;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

class AclRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Acl::class);
    }

    public function aclExists($user, $group, $location)
    {
        $qb = $this
            ->createQueryBuilder('a')
            ->where('a.user = :user')->setParameter('user', $user)
            ->andWhere('a.group = :group')->setParameter('group', $group)
        ;
        if ($location) {
            $qb->andWhere('a.location = :location')->setParameter('location', $location);
        }

        return null !== $qb->getQuery()->getOneOrNullResult();
    }

    public function userHasRoles(People $user, array $groups): bool
    {
        $qb = $this
            ->createQueryBuilder('a')
            ->leftJoin(Group::class, 'g', Join::WITH, 'g.id = a.group')
            ->where('a.user = :user')->setParameter('user', $user)
            ->andWhere('g.name IN (:groups)')->setParameter('groups', $groups)
        ;

        return !empty($qb->getQuery()->getResult());
    }

    public function importAcls(User $user, array $acls, ?Location $location)
    {
        foreach ($acls as $acl) {
            $new = (new Acl())
                ->setUser($user)
                ->setGroup($acl->getGroup())
                ->setLocation($location ?? $acl->getLocation());
            $this->getEntityManager()->persist($new);
        }
    }

    public function removeAclByGroup(User $user, Group $group)
    {
        $acls = $this->findBy([
            'user' => $user,
            'group' => $group,
        ]);

        foreach ($acls as $acl) {
            $this->getEntityManager()->remove($acl);
        }
    }

    public function removeAclByUser(User $user)
    {
        $acls = $this->findBy([
            'user' => $user,
        ]);

        foreach ($acls as $acl) {
            $this->getEntityManager()->remove($acl);
        }
    }

    public function getIdentifiersForGroup(Group $group): array
    {
        $qb = $this->createQueryBuilder('acl');

        $qb
            ->select('acl.id')
            ->where('acl.group = :group')
            ->setParameter('group', $group)
        ;

        return $qb->getQuery()->getScalarResult();
    }

    public function getUserGroupsForIntranet(User $user): array
    {
        $acls = $this->createQueryBuilder('acl')
            ->select('g.name')
            ->addSelect('l.id location_id')
            ->where('acl.user = :user')
            ->innerJoin('acl.group', 'g')
            ->leftJoin('acl.location', 'l')
            ->addOrderBy('g.name')
            ->addOrderBy('l.id')
            ->setParameter('user', $user)
            ->getQuery()->getScalarResult();

        $results = [];
        foreach ($acls as $acl) {
            $results[] = $acl['name'];
            if (null !== $acl['location_id']) {
                $results[] = \sprintf('%s_%d', $acl['name'], $acl['location_id']);
            }
        }

        return array_values(array_unique($results));
    }

    public function updatePeopleAclByGroup(People $people, Group $group)
    {
        $acl = (new Acl())
            ->setUser($people)
            ->setGroup($group)
            ->setLocation($people->getBusinessUnit()->getLocation());

        $this->getEntityManager()->persist($acl);
    }

    public function getExpiredAcls(): array
    {
        $queryBuilder = $this->createQueryBuilder('acl')
            ->where('acl.expiredAt < :today')
            ->setParameter('today', (new \DateTime())->format('Y-m-d'))
        ;

        return $queryBuilder->getQuery()->getResult();
    }
}
