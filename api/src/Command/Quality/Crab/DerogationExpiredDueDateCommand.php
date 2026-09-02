<?php

declare(strict_types=1);

namespace App\Command\Quality\Crab;

use App\Entity\Quality\Derogation;
use App\Notifier\Quality\Crab\CrabNotifier;
use App\Repository\Quality\Crab\DerogationRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'tld:derogation:expired')]
class DerogationExpiredDueDateCommand extends Command
{
    public function __construct(
        private readonly DerogationRepository $repository,
        private readonly CrabNotifier $notifier,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var Derogation $derogation */
        foreach ($this->repository->findExpiredDerogation() as $derogation) {
            $this->notifier->sendDerogationExpired($derogation);
        }

        return Command::SUCCESS;
    }
}
