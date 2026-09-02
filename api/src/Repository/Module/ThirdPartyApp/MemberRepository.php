<?php

declare(strict_types=1);

namespace App\Repository\Module\ThirdPartyApp;

use App\Entity\Module\ThirdPartyApp\Member;
use App\Entity\Module\ThirdPartyApp\Type\Extended;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Member::class);
    }

    public function findAdminsOfThirdPartyApp(Extended $thirdPartyApp)
    {
        $queryBuilder = $this->createQueryBuilder('members');

        $queryBuilder
            ->where('members.admin = 1')
            ->andWhere('members.thirdPartyApp = :thirdPartyApp')
            ->setParameter('thirdPartyApp', $thirdPartyApp);

        return $queryBuilder->getQuery()->getResult();
    }
}
