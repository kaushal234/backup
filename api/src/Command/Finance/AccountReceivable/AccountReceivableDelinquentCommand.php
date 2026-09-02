<?php

declare(strict_types=1);

namespace App\Command\Finance\AccountReceivable;

use App\Message\Finance\AccountReceivableDelinquent;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(name: 'api:finance:delinquent_ar')]
class AccountReceivableDelinquentCommand extends Command
{
    private readonly MessageBusInterface $bus;

    public function __construct(MessageBusInterface $bus)
    {
        parent::__construct();
        $this->setDescription('Set Account Receivables delinquent when it should be');

        $this->bus = $bus;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->bus->dispatch(new AccountReceivableDelinquent());

        return 0;
    }
}
