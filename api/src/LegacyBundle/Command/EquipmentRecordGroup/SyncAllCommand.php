<?php

declare(strict_types=1);

namespace LegacyBundle\Command\EquipmentRecordGroup;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;

#[AsCommand(
    name: 'legacy:er:group:sync:all',
    description: 'Sync Jobs linked to legacy equipments records from legacy to api'
)]
class SyncAllCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $commandNames = [
            'equipments',
            'sor_transactions',
            'sor_lines',
            'sor_units',
        ];

        $stopwatch = new Stopwatch();
        $stopwatch->start('er_sync_command');

        foreach ($commandNames as $commandName) {
            $command = $this->getApplication()->find(\sprintf('legacy:er:group:sync:%s', $commandName));
            $output->writeln(\sprintf('<info>Running synchronization command for <comment>%s</comment></info>', $commandName));
            $command->run($input, $output);
        }

        $stopwatch->stop('er_sync_command');
        $output->writeln(\sprintf('ER sync commands completed in %s s', $stopwatch->getEvent('er_sync_command')->getDuration() / 1000));

        return Command::SUCCESS;
    }
}
