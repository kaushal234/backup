<?php

declare(strict_types=1);

namespace App\Command\Sales\Customer;

use App\Notifier\Sales\Customer\CustomerNotifier;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:customers:watch_list_report')]
class CustomerWatchListReportCommand extends Command
{
    public function __construct(private readonly CustomerNotifier $notifier)
    {
        parent::__construct();
        $this->setDescription('Send report to SAM with eCustomers on watch list without Customer/ERP Reference');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->notifier->sendWatchListReport();

        return 0;
    }
}
