<?php

declare(strict_types=1);

namespace App\Javelo\EventListener;

use App\Javelo\Event\GroupUpdateEvent;
use App\Javelo\Notifier\Notifier;
use App\Javelo\Repository\GroupClientRepository;
use App\Javelo\Repository\GroupRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class GroupUpdateListener
{
    public function __construct(
        private readonly GroupClientRepository $groupClientRepository,
        private readonly GroupRepository $groupRepository,
        private readonly LoggerInterface $javeloRequestLogger,
        private readonly Notifier $notifier,
    ) {
    }

    public function __invoke(GroupUpdateEvent $event)
    {
        try {
            foreach ($this->groupRepository->getGroups() as $group) {
                if (!empty($group[$this->groupRepository::ADD_MEMBERS_KEY]) || !empty($group[$this->groupRepository::REMOVE_MEMBERS_KEY])) {
                    $this->groupClientRepository->updateGroup($group);
                }
            }
        } catch (\Exception $exception) {
            $this->javeloRequestLogger->error('Something went wrong when update groups on Javelo: {error}', [
                'error' => $exception->getMessage(),
            ]);
        }

        if (!$event->shouldSendMissingGroupMail()) {
            return;
        }

        $missingGroups = $this->groupRepository->getMissingGroups();
        if (!empty($missingGroups)) {
            try {
                $this->notifier->sendCreationGroupsRequest($missingGroups);
            } catch (\Exception $exception) {
                $this->javeloRequestLogger->error('Something went wrong when sending missing group creation email: {error}', [
                    'error' => $exception->getMessage(),
                ]);
            }
        }
    }
}
