<?php

declare(strict_types=1);

namespace App\Javelo\Command;

use App\Javelo\Notifier\Notifier;
use App\Repository\Common\LogRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:human-resources:javelo:notification')]
class NotificationCommand extends Command
{
    public function __construct(
        private readonly LogRepository $logRepository,
        private readonly Notifier $notifier,
        private readonly LoggerInterface $javeloRequestLogger,
    ) {
        parent::__construct();
        $this
            ->setDescription('Send notifications about Javelo')
            ->addArgument(
                'period',
                InputArgument::REQUIRED,
                'The period for notifications (daily or monthly)',
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $period = $input->getArgument('period');
        $startDate = new \DateTime();
        $endDate = new \DateTime();

        if ('daily' === $period) {
            $startDate->setTime(0, 0, 0);
            $endDate->setTime(23, 59, 59);
        } elseif ('monthly' === $period) {
            $startDate->modify('first day of previous month')->setTime(0, 0, 0);
            $endDate->modify('last day of previous month')->setTime(23, 59, 59);
        } else {
            $output->writeln('<error>Invalid period specified. Use "daily" or "monthly".</error>');

            return Command::INVALID;
        }

        $logs = $this->logRepository->findByPeriod('javelo', $startDate, $endDate);
        $countLogs = \count($logs);
        $output->writeln('<info>Logs found: '.$countLogs.'</info>');
        if ($countLogs > 0) {
            try {
                $this->notifier->sendMonitoring('Javelo Monitoring', $logs);
            } catch (\Exception $exception) {
                $this->javeloRequestLogger->error('Something went wrong sending monitoring on {toDay}: {error}', [
                    'error' => $exception->getMessage(),
                    'toDay' => (new \DateTime())->format('Y-m-d'),
                ]);
            }
        }

        return Command::SUCCESS;
    }
}
