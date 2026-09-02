<?php

declare(strict_types=1);

namespace App\Agile;

use App\Agile\Repository\UserRepository;
use App\Entity\Directory\People;
use App\Repository\Directory\PeopleRepository;
use Psr\Log\LoggerInterface;

class UserSyncProcessor
{
    private array $agileUsers = [];
    private array $peopleConcerned = [];
    private bool $initialized = false;

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly PeopleRepository $peopleRepository,
        private readonly UserSyncContext $userSyncContext,
        private readonly LoggerInterface $agileRequestLogger
    ) {
    }

    /**
     * Return a synchronization context if needed (otherwise returns null).
     */
    public function resolveSyncContext(People $people): ?UserSyncContext
    {
        $this->initialize();

        $previousAgileUser = $this->userRepository->searchAgileUserConcerned($people, $this->agileUsers);
        $isEligibleForSync = \in_array($people->getId(), array_column($this->peopleConcerned, 'id'), true)
            && !$people->isDisabled();

        $context = $this->userSyncContext->buildContext(
            $people,
            $isEligibleForSync,
            $this->agileUsers,
            $previousAgileUser
        );

        return null !== $context->getResolvedEvent() ? $context : null;
    }

    public function executeSync(UserSyncContext $context): void
    {
        $resolvedEvent = $context->getResolvedEvent();

        try {
            $this->userRepository->updateAgileUser(
                $context->getAgileUserToUpdate(),
                $context->getChanges(),
                $resolvedEvent
            );
        } catch (\Throwable $exception) {
            $this->agileRequestLogger->error(
                'Something went wrong when updating {people} on Agile: {error}',
                [
                    'people' => '#'.$context->getAgileUserToUpdate()->peopleId.' - '.$context->getAgileUserToUpdate()->lastName.' - '.$context->getAgileUserToUpdate()->firstName,
                    'error' => $exception->getMessage(),
                ]
            );
        }
    }

    private function initialize(): void
    {
        if ($this->initialized) {
            return;
        }

        $this->agileUsers = $this->userRepository->getAllUsers();
        $this->peopleConcerned = $this->peopleRepository->searchAllPeopleIdWithGroup('ACL_AUTH_AGILE');
        $this->initialized = true;
    }
}
