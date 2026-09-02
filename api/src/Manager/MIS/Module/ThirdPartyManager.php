<?php

declare(strict_types=1);

namespace App\Manager\MIS\Module;

use App\Entity\Directory\People;
use App\Entity\Directory\Position;
use App\Entity\Module\ThirdPartyApp\BusinessUnitPosition;
use App\Entity\Module\ThirdPartyApp\Member;
use App\Entity\Module\ThirdPartyApp\Type\Extended;
use App\Entity\Module\ThirdPartyApp\UpdateTask;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class ThirdPartyManager
{
    public const USER_ACCESS_GRANT_DELAY = '+4 days';

    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Create grand access for people by business unit position.
     * User should not be a member.
     * Remove all update tasks in progress before create new one.
     */
    public function createGrantAccessTasksByBusinessUnitPosition(BusinessUnitPosition $businessUnitPosition): void
    {
        $thirdPartyAppRepository = $this->entityManager->getRepository(Extended::class);
        $updateTaskRepository = $this->entityManager->getRepository(UpdateTask::class);

        $users = $thirdPartyAppRepository->findUsersByBusinessUnitPosition($businessUnitPosition->getBusinessUnit(), $businessUnitPosition->getPosition());

        foreach ($users as $user) {
            // Remove all in progress update tasks of user
            $updateTaskRepository->deleteOpenedByModuleAndUser($businessUnitPosition->getThirdPartyApp(), $user);

            // Skip if the user is already a member.
            $member = $thirdPartyAppRepository->findMember($businessUnitPosition->getThirdPartyApp(), $user);
            if ($member instanceof Member) {
                continue;
            }

            // Skip if the user is blacklist.
            if ($thirdPartyAppRepository->isUserBlacklisted($businessUnitPosition->getThirdPartyApp(), $user)) {
                continue;
            }

            $this->createUpdateTask($businessUnitPosition->getThirdPartyApp(), $user, UpdateTask::GRANT_ACCESS, UpdateTask::ORIGIN_TYPE_BUSINESS_UNIT_POSITION);
        }

        $this->entityManager->flush();
    }

    /**
     * Find members by business unit/position
     * And create REMOVE_ACCESS update tasks for those are not whitelist.
     * Remove all update tasks in progress before create new one.
     */
    public function createRemoveAccessTasksByBusinessUnitPosition(BusinessUnitPosition $businessUnitPosition): void
    {
        $thirdPartyAppRepository = $this->entityManager->getRepository(Extended::class);
        $updateTaskRepository = $this->entityManager->getRepository(UpdateTask::class);

        // Remove all in progress update tasks of module by business unit position.
        $updateTaskRepository->deleteOpenedByBusinessUnitPosition($businessUnitPosition->getThirdPartyApp(), $businessUnitPosition);

        $members = $thirdPartyAppRepository->findMembersByBusinessUnitPosition($businessUnitPosition->getThirdPartyApp(), $businessUnitPosition->getBusinessUnit(), $businessUnitPosition->getPosition());
        foreach ($members as $member) {
            // Remove all in progress update tasks of user
            $updateTaskRepository->deleteOpenedByModuleAndUser($businessUnitPosition->getThirdPartyApp(), $member->getUser());

            // Skip removing if the user is whitelisted.
            if ($thirdPartyAppRepository->isUserWhitelisted($businessUnitPosition->getThirdPartyApp(), $member->getUser())) {
                continue;
            }

            $this->createUpdateTask($businessUnitPosition->getThirdPartyApp(), $member->getUser(), UpdateTask::REMOVE_ACCESS, UpdateTask::ORIGIN_TYPE_BUSINESS_UNIT_POSITION);
        }

        $this->entityManager->flush();
    }

    /**
     *  If the user has no update task in progress and if he's not in the member list
     *  Then we create an update task with GRANT ACCESS demand.
     */
    public function createGrantAccessTaskByWhitelistUser(Extended $thirdPartyApp, User $user): void
    {
        $thirdPartyAppRepository = $this->entityManager->getRepository(Extended::class);
        $updateTaskRepository = $this->entityManager->getRepository(UpdateTask::class);

        $updateTaskRepository->deleteOpenedByModuleAndUser($thirdPartyApp, $user);

        $member = $thirdPartyAppRepository->findMember($thirdPartyApp, $user);
        $updateTaskInProgress = $thirdPartyAppRepository->findUpdateTaskOpened($thirdPartyApp, $user);

        if (!$updateTaskInProgress instanceof UpdateTask && !$member instanceof Member) {
            $this->createUpdateTask($thirdPartyApp, $user, UpdateTask::GRANT_ACCESS, UpdateTask::ORIGIN_TYPE_WHITELIST);
            $this->entityManager->flush();
        }
    }

    /**
     * Create REMOVE_ACCESS update task if user is actual member,
     * he's not in business unit position configuration.
     * Remove all update tasks in progress before create new one.
     * Method use in case of removing whitelisted user.
     */
    public function createRemoveAccessTaskByWhitelistedUser(Extended $thirdPartyApp, User $user): void
    {
        $thirdPartyAppRepository = $this->entityManager->getRepository(Extended::class);
        $businessUnitPositionRepository = $this->entityManager->getRepository(BusinessUnitPosition::class);
        $updateTaskRepository = $this->entityManager->getRepository(UpdateTask::class);

        $updateTaskRepository->deleteOpenedByModuleAndUser($thirdPartyApp, $user);

        $member = $thirdPartyAppRepository->findMember($thirdPartyApp, $user);
        $businessUnitPosition = $businessUnitPositionRepository->findOneBy([
            'thirdPartyApp' => $thirdPartyApp,
            'businessUnit' => $user->getBusinessUnit(),
            'position' => $user->getPosition(),
        ]);

        if ($member instanceof Member && null === $businessUnitPosition) {
            $this->createUpdateTask($thirdPartyApp, $user, UpdateTask::REMOVE_ACCESS, UpdateTask::ORIGIN_TYPE_WHITELIST);
            $this->entityManager->flush();
        }
    }

    /**
     *  If the user is not in the member list,
     *  and he's in business unit configuration, or he's whitelisted.
     *  Remove all update tasks in progress before create new one.
     *  Then we create an update task with GRANT ACCESS demand.
     */
    public function createGrantAccessTaskByBlacklistedUser(Extended $thirdPartyApp, User $user): void
    {
        $thirdPartyAppRepository = $this->entityManager->getRepository(Extended::class);
        $businessUnitPositionRepository = $this->entityManager->getRepository(BusinessUnitPosition::class);
        $updateTaskRepository = $this->entityManager->getRepository(UpdateTask::class);

        $updateTaskRepository->deleteOpenedByModuleAndUser($thirdPartyApp, $user);

        $member = $thirdPartyAppRepository->findMember($thirdPartyApp, $user);
        $businessUnitPosition = $businessUnitPositionRepository->findOneBy([
            'thirdPartyApp' => $thirdPartyApp,
            'businessUnit' => $user->getBusinessUnit(),
            'position' => $user->getPosition(),
        ]);

        if (!$member instanceof Member
            && ($businessUnitPosition instanceof BusinessUnitPosition
            || $thirdPartyAppRepository->isUserWhitelisted($thirdPartyApp, $user))) {
            $this->createUpdateTask($thirdPartyApp, $user, UpdateTask::GRANT_ACCESS, UpdateTask::ORIGIN_TYPE_BLACKLIST);
            $this->entityManager->flush();
        }
    }

    /**
     * Create REMOVE_ACCESS update task if user is actual member,
     * he's not in business unit position configuration,
     * Remove all update tasks in progress before create new one.
     * Method use in case of removing whitelisted user.
     */
    public function createRemoveAccessTaskByBlacklistUser(Extended $thirdPartyApp, User $user): void
    {
        $thirdPartyAppRepository = $this->entityManager->getRepository(Extended::class);
        $updateTaskRepository = $this->entityManager->getRepository(UpdateTask::class);

        $updateTaskRepository->deleteOpenedByModuleAndUser($thirdPartyApp, $user);

        $member = $thirdPartyAppRepository->findMember($thirdPartyApp, $user);

        if ($member instanceof Member) {
            $this->createUpdateTask($thirdPartyApp, $user, UpdateTask::REMOVE_ACCESS, UpdateTask::ORIGIN_TYPE_BLACKLIST);
            $this->entityManager->flush();
        }
    }

    /**
     * For each third party app, check if the user need a new ACCESS_GRANTED update task.
     */
    public function createGrantAccessTasksByUser(User $user): void
    {
        $thirdPartyAppRepository = $this->entityManager->getRepository(Extended::class);
        $businessUnitPositionRepository = $this->entityManager->getRepository(BusinessUnitPosition::class);
        $updateTaskRepository = $this->entityManager->getRepository(UpdateTask::class);

        $updateTasks = [];
        foreach ($thirdPartyAppRepository->findAll() as $thirdPartyApp) {
            $updateTaskRepository->deleteOpenedByModuleAndUser($thirdPartyApp, $user);

            // Skip if the user is not eligible
            // This will delete opened update tasks of disabled user
            if (!$this->isEligibleForAccess($user)) {
                continue;
            }

            // Skip if the user is already a member.
            $member = $thirdPartyAppRepository->findMember($thirdPartyApp, $user);
            if ($member instanceof Member) {
                continue;
            }

            // Skip if the user is blacklisted.
            if ($thirdPartyAppRepository->isUserBlacklisted($thirdPartyApp, $user)) {
                continue;
            }

            // Create tasks if the user is whitelist OR he's in business unit/position configuration.
            $isWhitelist = $thirdPartyAppRepository->isUserWhitelisted($thirdPartyApp, $user);
            $businessUnitPosition = $businessUnitPositionRepository->findOneBy([
                'thirdPartyApp' => $thirdPartyApp,
                'businessUnit' => $user->getBusinessUnit(),
                'position' => $user->getPosition(),
            ]);

            if ($isWhitelist) {
                $this->createUpdateTask($thirdPartyApp, $user, UpdateTask::GRANT_ACCESS, UpdateTask::ORIGIN_TYPE_WHITELIST);
            } elseif ($businessUnitPosition) {
                $this->createUpdateTask($thirdPartyApp, $user, UpdateTask::GRANT_ACCESS, UpdateTask::ORIGIN_TYPE_BUSINESS_UNIT_POSITION);
            }
        }

        $this->entityManager->flush();
    }

    /**
     * For each third party app of the user,
     * Delete all in progress update tasks.
     * And then create REMOVE_ACCESS for each app.
     * Method use in case of disabled user.
     */
    public function createRemoveAccessTasksByUser(User $user): void
    {
        $thirdPartyAppRepository = $this->entityManager->getRepository(Extended::class);
        $updateTaskRepository = $this->entityManager->getRepository(UpdateTask::class);
        $businessUnitPositionRepository = $this->entityManager->getRepository(BusinessUnitPosition::class);

        foreach ($thirdPartyAppRepository->findByMember($user) as $thirdPartyApp) {
            $updateTaskRepository->deleteOpenedByModuleAndUser($thirdPartyApp, $user);

            // Create REMOVE tasks if the user is blacklist OR he's not in business unit/position configuration.
            $isBlacklist = $thirdPartyAppRepository->isUserBlacklisted($thirdPartyApp, $user);
            $isWhitelist = $thirdPartyAppRepository->isUserWhitelisted($thirdPartyApp, $user);
            $businessUnitPosition = $businessUnitPositionRepository->findOneBy([
                'thirdPartyApp' => $thirdPartyApp,
                'businessUnit' => $user->getBusinessUnit(),
                'position' => $user->getPosition(),
            ]);

            if (!$this->isEligibleForAccess($user)) {
                $this->createUpdateTask($thirdPartyApp, $user, UpdateTask::REMOVE_ACCESS, UpdateTask::ORIGIN_TYPE_MANUAL);
            } elseif ($isBlacklist) {
                $this->createUpdateTask($thirdPartyApp, $user, UpdateTask::REMOVE_ACCESS, UpdateTask::ORIGIN_TYPE_BLACKLIST);
            } elseif (!$businessUnitPosition && !$isWhitelist) {
                $this->createUpdateTask($thirdPartyApp, $user, UpdateTask::REMOVE_ACCESS, UpdateTask::ORIGIN_TYPE_BUSINESS_UNIT_POSITION);
            }
        }

        $this->entityManager->flush();
    }

    public function isEligibleForAccess(User $user): bool
    {
        return $user->isEnabled()
            || ($user->getEnableAt()
                && $user->getEnableAt() <= (new \DateTime(self::USER_ACCESS_GRANT_DELAY))->setTime(23, 59, 59)
                && $user->getEnableAt() >= (new \DateTime())->setTime(0, 0, 0));
    }

    protected function createUpdateTask(Extended $thirdPartyApp, User $user, string $demandType, ?string $originType = null): void
    {
        $updateTask = new UpdateTask();
        $updateTask->thirdPartyApp = $thirdPartyApp;
        $updateTask->user = $user;
        $updateTask->demandType = $demandType;
        $updateTask->originType = $originType;

        $this->entityManager->persist($updateTask);
    }
}
