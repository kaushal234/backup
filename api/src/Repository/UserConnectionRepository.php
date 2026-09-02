<?php

declare(strict_types=1);

namespace App\Repository;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Activity\UserConnection;
use App\Entity\Sales\Customer;
use App\Entity\Sales\ExtranetUser;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

class UserConnectionRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly IriConverterInterface $iriConverter
    ) {
        parent::__construct($registry, UserConnection::class);
    }

    public function getExtranetUsersConnectionsForLastMonth()
    {
        $qb = $this->createQueryBuilder('uc');

        return $qb
            ->select('xu.lastname AS lastname')
            ->addSelect('xu.firstname AS firstname')
            ->addSelect('xu.id AS id')
            ->addSelect('xu.email AS email')
            ->addSelect('c.name as customer')
            ->addSelect(\sprintf('%s as nbConnections', $qb->expr()->count('uc')))
            ->innerJoin(ExtranetUser::class, 'xu', Join::WITH, 'uc.user = xu.id')
            ->leftJoin('xu.extranetUserProfile', 'xu_profile')
            ->leftJoin(Customer::class, 'c', Join::WITH, 'c.id = xu_profile.customer')
            ->where($qb->expr()->gte('uc.createdAt', ':first_day_last_month'))
            ->andWhere($qb->expr()->lte('uc.createdAt', ':last_day_last_month'))
            ->groupBy('xu.id')
            ->setParameter('first_day_last_month', new \DateTime('first day of last month'))
            ->setParameter('last_day_last_month', new \DateTime('last day of last month'))
            ->getQuery()
            ->getResult()
        ;
    }

    public function addActivityLog(User $people, string $origin): void
    {
        $userConnection = (new UserConnection())
            ->setUser($people)
            ->setResource($this->iriConverter->getIriFromResource($people))
            ->setCreatedAt(new \DateTime())
        ;
        $userConnection->metadata = ['origin' => $origin];

        $entityManager = $this->getEntityManager();
        $entityManager->persist($userConnection);
        $entityManager->flush();
    }
}
