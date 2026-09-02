<?php

declare(strict_types=1);

namespace App\Repository\Module\ThirdPartyApp;

use App\Entity\Module\Module;
use App\Entity\Module\ThirdPartyApp\Type\Light;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LightRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Light::class);
    }

    public function findAccountReviewNeeded(): array
    {
        $queryBuilder = $this->createQueryBuilder('thirdPartyApp');
        $queryBuilder->andWhere('thirdPartyApp.accountReviewDateStart IS NOT NULL');
        $queryBuilder->andWhere('thirdPartyApp.accountReviewFrequency IS NOT NULL AND thirdPartyApp.accountReviewFrequency > 0');
        $queryBuilder->andWhere('thirdPartyApp.status != :disabled');
        $queryBuilder->setParameter('disabled', Module::DISABLED);

        return $queryBuilder->getQuery()->getResult();
    }

    public function findSecurityReviewNeeded(): array
    {
        $queryBuilder = $this->createQueryBuilder('thirdPartyApp');
        $queryBuilder->andWhere('thirdPartyApp.securityReviewDateStart IS NOT NULL');
        $queryBuilder->andWhere('thirdPartyApp.securityReviewFrequency IS NOT NULL AND thirdPartyApp.securityReviewFrequency > 0');
        $queryBuilder->andWhere('thirdPartyApp.status != :disabled');
        $queryBuilder->setParameter('disabled', Module::DISABLED);

        return $queryBuilder->getQuery()->getResult();
    }
}
