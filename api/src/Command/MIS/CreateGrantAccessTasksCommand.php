<?php

declare(strict_types=1);

namespace App\Command\MIS;

use App\Entity\Directory\People;
use App\Manager\MIS\Module\ThirdPartyManager;
use App\Repository\Module\ThirdPartyApp\ExtendedRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:mis:third_party_app:create_grant_access_tasks', description: 'Creates grant/remove update tasks for people')]
class CreateGrantAccessTasksCommand extends Command
{
    public function __construct(
        private readonly ExtendedRepository $thirdPartyAppRepository,
        private readonly ThirdPartyManager $thirdPartyManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var People $people */
        foreach ($this->thirdPartyAppRepository->findPeopleCurrentlyOrFutureEnabled() as $people) {
            $this->thirdPartyManager->createGrantAccessTasksByUser($people);
            $this->thirdPartyManager->createRemoveAccessTasksByUser($people);
        }

        return Command::SUCCESS;
    }
}
