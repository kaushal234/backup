<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserPasswordLog;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;

class UserPasswordLogRepository extends EntityRepository
{
    /**
     * @return array|UserPasswordLog[]
     */
    public function getPreviousPasswords(User $user, int $limit): array
    {
        $qb = $this->createQueryBuilder('upl');

        $qb
            ->setMaxResults($limit)
            ->orderBy('upl.createdAt', Criteria::DESC)
            ->where('upl.user = :user')
            ->setParameter('user', $user)
        ;

        return $qb->getQuery()->getResult();
    }
}
