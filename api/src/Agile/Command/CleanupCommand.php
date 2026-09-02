<?php

declare(strict_types=1);

namespace App\Agile\Command;

use App\Agile\UserEventResolver;
use App\Agile\UserSyncProcessor;
use App\Repository\Directory\PeopleRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:human-resources:agile:cleanup')]
class CleanupCommand extends Command
{
    public function __construct(
        private readonly PeopleRepository $peopleRepository,
        private readonly UserSyncProcessor $userSyncProcessor,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $joined = $updated = $suspended = 0;

        $allPeople = $this->peopleRepository->findAll();

        foreach ($allPeople as $people) {
            $context = $this->userSyncProcessor->resolveSyncContext($people);

            if (null === $context) {
                continue;
            }

            $this->userSyncProcessor->executeSync($context);

            match ($context->getResolvedEvent()) {
                UserEventResolver::USER_JOINED => $joined++,
                UserEventResolver::USER_UPDATED => $updated++,
                UserEventResolver::USER_SUSPENDED => $suspended++,
                default => null,
            };
        }

        $output->writeln('--- Agile synchronization completed ---');
        $output->writeln(\sprintf('%d people have joined Agile.', $joined));
        $output->writeln(\sprintf('%d people have been updated.', $updated));
        $output->writeln(\sprintf('%d people have been suspended.', $suspended));

        return Command::SUCCESS;
    }
}
