<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

#[AsCommand(name: 'legacy:import:all')]
class ImportAllCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
        $this->setDescription('Imports all from legacy tables');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // List of table to import (order matters!)
        $tables = [
            'acronyms',
            'directory:people',
            'directory:juridical-locations',
            'directory:locations',
            'directory:business-units',
            'directory:departments',
            'directory:positions',
            'directory:divisions',
            'directory:people-relations',
            'directory:people-missing',
            'acl',
            'modules',
            'news',
            'countries',
            'sales:customers',
            'sales:crt',
            'iata-codes',
            'sales:extranet_users',
            'emission_ratings',
            'sales:product_types',
            'sales:product_families',
            'sales:products',
            'sales:product_family_dms',
            'sales:competitors',
            'sales:competitors_files',
            'sales:master_sfr',
            'sales:sfr',
            'sales:sfr:comments',
            'sales:fcr',
            'sales:cpr',
            'sales:forecast_closure_files',
            'sales:sales_forecast_files',
            'sales:orders',
            'sales:orders:files',
            'finance:currency',
            'finance:exchange_rates',
            'sales:market_intelligences',
            'sales:market_intelligences:comments',
            'sales:market_intelligence_files',
            'sales:market_intelligence_subscriptions',
        ];

        /** @var ProcessHelper $processHelper */
        $processHelper = $this->getHelper('process');
        foreach ($tables as $table) {
            $output->writeln(\sprintf('<info>Running import for <comment>%s</comment></info>', $table));

            $process = new Process([$_SERVER['argv'][0], \sprintf('legacy:import:%s', $table)], null, null, null, 600);
            $processHelper->mustRun($output, $process);
        }

        return 0;
    }
}
