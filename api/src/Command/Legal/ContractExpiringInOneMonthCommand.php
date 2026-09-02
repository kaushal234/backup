<?php

declare(strict_types=1);

namespace App\Command\Legal;

use App\Entity\Legal\Contract;
use App\Notifier\Legal\ContractExpirationNotifier;
use App\Repository\Legal\ContractRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

#[AsCommand(
    name: 'api:legal:contract:expiring-in-one-month',
    description: 'Notify owner and supervisor of contracts expiring in one month'
)]
class ContractExpiringInOneMonthCommand extends Command
{
    public function __construct(
        private readonly ContractExpirationNotifier $notifier,
        private readonly ContractRepository $contractRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $contracts = $this->contractRepository->findContractsExpiringInOneMonth();

        if (empty($contracts)) {
            $output->writeln('No contracts expiring in one month');

            return Command::SUCCESS;
        }

        $notifiedCount = 0;

        foreach ($contracts as $contract) {
            /* @var Contract $contract */
            try {
                $this->notifier->notifyExpiringSoon($contract);
                ++$notifiedCount;
                $output->writeln(\sprintf(
                    '<info>Contract #%s "%s" expiring in one month, owner notified</info>',
                    $contract->getId(),
                    $contract->shortDescription
                ));
            } catch (TransportExceptionInterface $e) {
                $output->writeln(\sprintf(
                    '<error>Failed to send email for contract #%s "%s" expiring in one month: %s</error>',
                    $contract->getId(),
                    $contract->shortDescription,
                    $e->getMessage()
                ));
            }
        }

        $output->writeln(\sprintf('<info>%s contracts expiring in one month notified</info>', $notifiedCount));

        return Command::SUCCESS;
    }
}
