<?php

declare(strict_types=1);

namespace LegacyBundle\Command\Helper;

use Doctrine\ORM\EntityManagerInterface;

class EntityCacheHelperFactory
{
    private readonly EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function createEntityCache($repositoryName, $descrProperty)
    {
        return new EntityCacheHelper($this->em->getRepository($repositoryName), $descrProperty);
    }

    public function createFirstOrNullEntityCache($repositoryName, $descrProperty)
    {
        return new FirstOrNullEntityCacheHelper($this->em->getRepository($repositoryName), $descrProperty);
    }
}
