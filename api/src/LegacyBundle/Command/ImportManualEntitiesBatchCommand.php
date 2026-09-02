<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\ImportBatchHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:equipment:manuals:batch')]
class ImportManualEntitiesBatchCommand extends Command
{
    private readonly Connection $legacyConnection;
    private readonly ImportBatchHelper $importBatchHelper;

    public function __construct(Connection $legacyConnection, ImportBatchHelper $importBatchHelper)
    {
        parent::__construct();
        $this->setDescription('Import manuals from legacy - batch');
        $this->legacyConnection = $legacyConnection;
        $this->importBatchHelper = $importBatchHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var ProcessHelper $helper */
        $helper = $this->getHelper('process');
        $this->importBatchHelper->runBatch(
            $input,
            $output,
            ImportManualEntitiesCommand::getDefaultName(),
            (int) $this->legacyConnection->executeQuery('SELECT COUNT(*) FROM manuals')->fetchOne(),
            $helper,
            5000,
            200
        );

        return Command::SUCCESS;
    }
}
