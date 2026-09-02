<?php

declare(strict_types=1);

namespace App\Repository\Module\ThirdPartyApp;

use App\Entity\Module\Module;
use App\Entity\Module\ThirdPartyApp\BusinessUnitPosition;
use App\Entity\Module\ThirdPartyApp\Type\Extended;
use App\Entity\Module\ThirdPartyApp\UpdateTask;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UpdateTaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UpdateTask::class);
    }

    /**
     * Delete all update tasks by module.
     * Used when convert Extended Third Party App to Light.
     */
    public function deleteAllByModule(Module $module): void
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder
            ->delete(UpdateTask::class, 'updateTask')
            ->where('updateTask.thirdPartyApp = :module')
            ->setParameter('module', $module);

        $queryBuilder->getQuery()->execute();
    }

    /**
     * Delete all update tasks in Progress by module and user.
     * This method is used to clean user tasks to avoid duplicate tasks.
     */
    public function deleteOpenedByModuleAndUser(Extended $module, User $user): void
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder
            ->delete(UpdateTask::class, 'updateTask')
            ->where('updateTask.thirdPartyApp = :module')
            ->setParameter('module', $module)
            ->andWhere('updateTask.user = :user')
            ->setParameter('user', $user)
            ->andWhere('updateTask.done = 0')
        ;

        $queryBuilder->getQuery()->execute();
    }

    /**
     * Remove all in progress update tasks of module by business unit position.
     * This method is used to clean update tasks when removing business unit position.
     */
    public function deleteOpenedByBusinessUnitPosition(Extended $module, BusinessUnitPosition $businessUnitPosition): void
    {
        $userIds = $this->createQueryBuilder('updateTask')
            ->select('user.id')
            ->innerJoin('updateTask.user', 'user')
            ->where('updateTask.thirdPartyApp = :module')
            ->setParameter('module', $module)
            ->andWhere('user.businessUnit = :businessUnit')
            ->setParameter('businessUnit', $businessUnitPosition->getBusinessUnit())
            ->andWhere('user.position = :position')
            ->setParameter('position', $businessUnitPosition->getPosition())
            ->andWhere('updateTask.done = 0')
            ->getQuery()->getResult();

        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder
            ->delete(UpdateTask::class, 'updateTask')
            ->where('updateTask.user IN (:ids)')
            ->setParameter('ids', $userIds);

        $queryBuilder->getQuery()->execute();
    }
}
