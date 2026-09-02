<?php

declare(strict_types=1);

namespace App\Javelo\EventListener;

use App\Javelo\Event\GroupManagementEvent;
use App\Javelo\Repository\GroupRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[AsEventListener]
class GroupManagementListener
{
    private const EXCLUDE_GROUP_SYNCHRONIZATION = [    // Upper management doesn't want to be seen by lower-level HR
        'maxime.mahieu@smart-airport-systems.com' => 13934,
        'fabrice.denninger@aes-gse.com' => 11978,
    ];

    public function __construct(
        private readonly GroupRepository $groupRepository,
        private readonly LoggerInterface $javeloRequestLogger,
    ) {
    }

    public function __invoke(GroupManagementEvent $event): void
    {
        $javeloUser = $event->getJaveloUser();

        if (\in_array((int) $javeloUser->externalId, self::EXCLUDE_GROUP_SYNCHRONIZATION, true)) {
            return;
        }

        $this->groupRepository->initializeGroups();

        try {
            $groupName = $this->transformBusinessUnitNameToGroupName($javeloUser->businessUnit);
            if (!\array_key_exists($groupName, $this->groupRepository->getGroups())) {
                $this->groupRepository->addMissingGroup($groupName, $javeloUser->businessUnit, $javeloUser->division);
                throw new \InvalidArgumentException('Group does not exist, please create '.$groupName);
            }
            if (null === $javeloUser->id) {
                throw new \InvalidArgumentException('User ID is null after user creation.');
            }
            $groupsWithUser = $this->findGroupsByUser($javeloUser->id);
            if (!\in_array($groupName, $groupsWithUser, true)) {
                $this->groupRepository->addUserOnAddMembersList($groupName, $javeloUser->id);
            }
            foreach ($groupsWithUser as $group) {
                if ($group !== $groupName) {
                    $this->groupRepository->addUserOnRemoveMembersList($group, $javeloUser->id);
                }
            }
        } catch (\Exception $exception) {
            $this->javeloRequestLogger->error('Something went wrong when controlling group for {business_unit} for {javelo_username} on Javelo: {error}', [
                'javelo_username' => $javeloUser->userName,
                'business_unit' => (string) $javeloUser->businessUnit,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function transformBusinessUnitNameToGroupName(string $unit): string
    {
        $slugger = new AsciiSlugger();

        return 'BU_'.mb_strtoupper($slugger->slug(str_replace(['&'], ['_AND_'], $unit), '_')->toString());
    }

    private function findGroupsByUser(string $userId): array
    {
        $groupsWithUser = [];

        foreach ($this->groupRepository->getGroups() as $groupName => $group) {
            if (isset($group['members'][$userId])) {
                $groupsWithUser[] = $groupName;
            }
        }

        return $groupsWithUser;
    }
}
