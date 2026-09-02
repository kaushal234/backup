<?php

declare(strict_types=1);

namespace App\Repository\Module\ThirdPartyApp;

use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\People;
use App\Entity\Directory\Position;
use App\Entity\Module\ThirdPartyApp\Member;
use App\Entity\Module\ThirdPartyApp\Type\Extended;
use App\Entity\Module\ThirdPartyApp\UpdateTask;
use App\Entity\User;
use App\Manager\MIS\Module\ThirdPartyManager;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ExtendedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Extended::class);
    }

    public function findUsersByBusinessUnitPosition(BusinessUnit $businessUnit, Position $position): array
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder
            ->select('u')
            ->from(User::class, 'u')
            ->where('u.businessUnit = :businessUnit')
            ->andWhere('u.position = :position')
            ->andWhere(
                $queryBuilder->expr()->orX(
                    'u.disabled = 0',
                    $queryBuilder->expr()->andX(
                        'u.enableAt IS NOT NULL',
                        'u.enableAt >= :now',
                        'u.enableAt <= :userAccessGrantDelay'
                    )
                )
            )
            ->setParameter('businessUnit', $businessUnit)
            ->setParameter('position', $position)
            ->setParameter('now', (new \DateTime())->setTime(0, 0, 0)->format('Y-m-d H:i:s'))
            ->setParameter('userAccessGrantDelay', (new \DateTime(ThirdPartyManager::USER_ACCESS_GRANT_DELAY))->setTime(23, 59, 59)->format('Y-m-d H:i:s'))
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    public function findMember(Extended $thirdPartyApp, User $user): ?Member
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select('members')
            ->from(Member::class, 'members')
            ->innerJoin('members.user', 'user')
            ->where('members.thirdPartyApp = :thirdPartyApp')
            ->andWhere('user = :user')
            ->andWhere(
                $queryBuilder->expr()->orX(
                    'user.disabled = 0',
                    $queryBuilder->expr()->andX(
                        'user.enableAt IS NOT NULL',
                        'user.enableAt >= :now',
                        'user.enableAt <= :peopleAccessGrantDelay'
                    )
                )
            )
            ->groupBy('members.user')
            ->setParameter('thirdPartyApp', $thirdPartyApp)
            ->setParameter('user', $user)
            ->setParameter('now', (new \DateTime())->setTime(0, 0, 0)->format('Y-m-d H:i:s'))
            ->setParameter('peopleAccessGrantDelay', (new \DateTime(ThirdPartyManager::USER_ACCESS_GRANT_DELAY))->setTime(23, 59, 59)->format('Y-m-d H:i:s'))
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function findUpdateTaskOpened(Extended $thirdPartyApp, User $user, string $demandeType = UpdateTask::GRANT_ACCESS): ?UpdateTask
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select('updateTask')
            ->from(UpdateTask::class, 'updateTask')
            ->innerJoin('updateTask.user', 'user')
            ->where('updateTask.thirdPartyApp = :thirdPartyApp')
            ->andWhere('user = :user')
            ->andWhere(
                $queryBuilder->expr()->orX(
                    'user.disabled = 0',
                    $queryBuilder->expr()->andX(
                        'user.enableAt IS NOT NULL',
                        'user.enableAt >= :now',
                        'user.enableAt <= :peopleAccessGrantDelay'
                    )
                )
            )->andWhere('updateTask.demandType = :demandType')
            ->andWhere('updateTask.done = 0')
            ->setParameter('thirdPartyApp', $thirdPartyApp)
            ->setParameter('user', $user)
            ->setParameter('demandType', $demandeType)
            ->setParameter('now', (new \DateTime())->setTime(0, 0, 0)->format('Y-m-d H:i:s'))
            ->setParameter('peopleAccessGrantDelay', (new \DateTime(ThirdPartyManager::USER_ACCESS_GRANT_DELAY))->setTime(23, 59, 59)->format('Y-m-d H:i:s'))
            ->setMaxResults(1)
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function findMembersByBusinessUnitPosition(Extended $thirdPartyApp, BusinessUnit $businessUnit, Position $position): array
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder
            ->select('members')
            ->from(Member::class, 'members')
            ->innerJoin('members.user', 'user')
            ->innerJoin('members.thirdPartyApp', 'thirdPartyApp')
            ->where('members.thirdPartyApp = :thirdPartyApp')
            ->andWhere('user.businessUnit = :businessUnit')
            ->andWhere('user.position = :position')
            ->andWhere(
                $queryBuilder->expr()->orX(
                    'user.disabled = 0',
                    $queryBuilder->expr()->andX(
                        'user.enableAt IS NOT NULL',
                        'user.enableAt >= :now',
                        'user.enableAt <= :peopleAccessGrantDelay'
                    )
                )
            )
            ->setParameter('thirdPartyApp', $thirdPartyApp)
            ->setParameter('businessUnit', $businessUnit)
            ->setParameter('position', $position)
            ->setParameter('now', (new \DateTime())->setTime(0, 0, 0)->format('Y-m-d H:i:s'))
            ->setParameter('peopleAccessGrantDelay', (new \DateTime())->modify(ThirdPartyManager::USER_ACCESS_GRANT_DELAY)->setTime(23, 59, 59)->format('Y-m-d H:i:s'))
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    public function isUserWhitelisted(Extended $thirdPartyApp, User $user): bool
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select('COUNT(whitelistedUsers)')
            ->from(Extended::class, 'thirdPartyApp')
            ->innerJoin('thirdPartyApp.whitelistedUsers', 'whitelistedUsers')
            ->where('thirdPartyApp = :thirdPartyApp')
            ->andWhere('whitelistedUsers = :user')
            ->setParameter('thirdPartyApp', $thirdPartyApp)
            ->setParameter('user', $user);

        return (bool) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    public function isUserBlacklisted(Extended $thirdPartyApp, User $user): bool
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select('COUNT(blacklistedUsers)')
            ->from(Extended::class, 'thirdPartyApp')
            ->innerJoin('thirdPartyApp.blacklistedUsers', 'blacklistedUsers')
            ->where('thirdPartyApp = :thirdPartyApp')
            ->andWhere('blacklistedUsers = :user')
            ->setParameter('thirdPartyApp', $thirdPartyApp)
            ->setParameter('user', $user);

        return (bool) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * Find all third party apps when the user is member.
     *
     * @return array<Extended>
     */
    public function findByMember(User $user): array
    {
        $queryBuilder = $this->createQueryBuilder('thirdPartyApp');
        $queryBuilder
            ->innerJoin('thirdPartyApp.members', 'members')
            ->innerJoin('members.user', 'user')
            ->where('user = :user')
            ->setParameter('user', $user)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    public function findThirdPartyAppsByAdmin(People $user): array
    {
        $queryBuilder = $this->createQueryBuilder('thirdPartyApp');

        $queryBuilder
            ->innerJoin('thirdPartyApp.members', 'members')
            ->where('members.admin = 1')
            ->andWhere('members.user = :user')
            ->setParameter('user', $user)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    // Find all third party app with update tasks opened.
    public function findByUpdateTasksOpened(): array
    {
        $queryBuilder = $this->createQueryBuilder('thirdPartyApp');

        $queryBuilder
            ->innerJoin('thirdPartyApp.updateTasks', 'updateTasks')
            ->where('updateTasks.done = 0');

        return $queryBuilder->getQuery()->getResult();
    }

    public function findPeopleCurrentlyOrFutureEnabled(): array
    {
        $queryBuilder = $this->createQueryBuilder('thirdPartyApp');

        $queryBuilder
            ->select('people')
            ->from(People::class, 'people')
            ->andWhere(
                $queryBuilder->expr()->orX(
                    'people.disabled = 0',
                    $queryBuilder->expr()->andX(
                        'people.enableAt IS NOT NULL',
                        'people.enableAt >= :now',
                        'people.enableAt <= :peopleAccessGrantDelay'
                    )
                )
            )
            ->setParameter('now', (new \DateTime())->setTime(0, 0, 0)->format('Y-m-d H:i:s'))
            ->setParameter('peopleAccessGrantDelay', (new \DateTime())->modify(ThirdPartyManager::USER_ACCESS_GRANT_DELAY)->setTime(23, 59, 59)->format('Y-m-d H:i:s'))
        ;

        return $queryBuilder->getQuery()->getResult();
    }
}
