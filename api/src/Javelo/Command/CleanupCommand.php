<?php

declare(strict_types=1);

namespace App\Javelo\Command;

use App\Javelo\Event\GroupManagementEvent;
use App\Javelo\Event\GroupUpdateEvent;
use App\Javelo\Event\UserCreatedEvent;
use App\Javelo\Event\UserUpdatedEvent;
use App\Javelo\Factory\UserFactory;
use App\Javelo\Repository\UserClientRepository;
use App\Javelo\Repository\UserRepository;
use App\Javelo\Resources\User;
use App\Javelo\UserComparator;
use App\Repository\Directory\PeopleRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

#[AsCommand(name: 'api:human-resources:javelo:cleanup')]
class CleanupCommand extends Command
{
    public function __construct(
        private readonly UserClientRepository $userClientRepository,
        private readonly UserRepository $userRepository,
        private readonly PeopleRepository $peopleRepository,
        private readonly UserComparator $userComparator,
        private readonly UserFactory $javeloUserFactory,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $created = $updated = 0;
        $synchronizedPeopleId = $this->userRepository->searchPeopleWithAclAuthJavelo();
        $javeloUsers = $this->userClientRepository->getAllUsers();
        $allPeople = $this->peopleRepository->findAll();

        $progressBar = new ProgressBar($output, \count($allPeople));

        foreach ($allPeople as $people) {
            $progressBar->advance();
            $previousJaveloUser = $this->userRepository->searchJaveloUserConcerned($people, $javeloUsers);
            $javeloUserToUpdate = $this->javeloUserFactory->createFromPeople($people, $previousJaveloUser?->id, $synchronizedPeopleId);
            if (
                $previousJaveloUser instanceof User
                && false === $previousJaveloUser->active
                && !\in_array($people->getId(), array_column($synchronizedPeopleId, 'id'), true)
            ) {
                // No useless synchronization when the user is already inactive on Javelo
                // and is no longer eligible for sync (missing ACL_AUTH_JAVELO group).
                continue;
            }
            $changes = $this->userComparator->getChanges($javeloUserToUpdate, $previousJaveloUser);

            if ($previousJaveloUser instanceof User && !empty($changes)) {
                // Add sleep to avoid exception 429 (max 300 requests in 5 min)
                sleep(1);
                $this->eventDispatcher->dispatch(new UserUpdatedEvent($javeloUserToUpdate, $changes));
                ++$updated;
                $output->writeln(\sprintf('people  #%d (%s %s) updated on javelo', $people->getId(), $people->getFirstname(), $people->getLastname()));
            }

            if ($javeloUserToUpdate->active && null === $previousJaveloUser) {
                $this->eventDispatcher->dispatch(new UserCreatedEvent($javeloUserToUpdate, $changes));
                ++$created;
                $output->writeln(\sprintf('people #%d (%s %s) created on javelo', $people->getId(), $people->getFirstname(), $people->getLastname()));
            }

            if ($javeloUserToUpdate->active) {
                $this->eventDispatcher->dispatch(new GroupManagementEvent($javeloUserToUpdate));
            }
        }

        $this->eventDispatcher->dispatch(new GroupUpdateEvent(sendMissingGroupMail: true));

        $progressBar->finish();
        $output->writeln('...');
        $output->writeln(\sprintf('%d people created on javelo.', $created));
        $output->writeln(\sprintf('%d people updated on javelo.', $updated));

        return Command::SUCCESS;
    }
}
