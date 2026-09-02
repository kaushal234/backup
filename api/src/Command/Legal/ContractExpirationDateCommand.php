<?php

declare(strict_types=1);

namespace App\Command\Legal;

use App\Entity\Legal\Contract;
use App\Notifier\Legal\ContractExpirationNotifier;
use App\Repository\Legal\ContractRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

#[AsCommand(
    name: 'api:legal:contract:expiration',
    description: 'Update contract status to EXPIRED when expiration date is passed'
)]
class ContractExpirationDateCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ContractExpirationNotifier $notifier,
        private readonly ContractRepository $contractRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $contracts = $this->contractRepository->findExpiredContracts();

        if (empty($contracts)) {
            $output->writeln('No contracts to expire');

            return Command::SUCCESS;
        }

        $expiredCount = 0;

        foreach ($contracts as $contract) {
            /* @var Contract $contract */
            $contract->status = Contract::EXPIRED;

            // Send email notification
            try {
                $this->notifier->notifyExpiration($contract);
                ++$expiredCount;
                $output->writeln(\sprintf(
                    '<info>Contract #%s "%s" marked as expired and owner notified</info>',
                    $contract->getId(),
                    $contract->shortDescription
                ));
            } catch (TransportExceptionInterface $e) {
                $output->writeln(\sprintf(
                    '<error>Failed to send email for expired contract #%s "%s": %s</error>',
                    $contract->getId(),
                    $contract->shortDescription,
                    $e->getMessage()
                ));
            }
        }

        $this->entityManager->flush();
        $output->writeln(\sprintf('<info>%s contracts expired</info>', $expiredCount));

        return Command::SUCCESS;
    }
}
