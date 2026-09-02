<?php

declare(strict_types=1);

namespace App\Command\TLDGroupSync;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'tld:group:sync:all')]
class TLDGroupSyncAllCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
        $this->setDescription('Sync All resources to TLD Group Wordpress database');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $resources = [
            'locations',
            'regions',
            'countries',
            'sales_areas',
            'catalogue',
            'people',
            'jobs',
        ];

        foreach ($resources as $resource) {
            $command = $this->getApplication()->find(\sprintf('tld:group:sync:%s', $resource));

            $output->writeln(\sprintf('<info>Running synchronization for <comment>%s</comment></info>', $resource));
            $command->run($input, $output);
        }

        return 0;
    }
}
