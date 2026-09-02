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

#[AsCommand(name: 'legacy:import:equipment:serials:batch')]
class ImportEquipmentSerialsBatchCommand extends Command
{
    private readonly Connection $legacyConnection;
    private readonly ImportBatchHelper $importBatchHelper;

    public function __construct(Connection $legacyConnection, ImportBatchHelper $importBatchHelper)
    {
        parent::__construct();
        $this
            ->setDescription('Import equipment serials from legacy - batch')
        ;
        $this->legacyConnection = $legacyConnection;
        $this->importBatchHelper = $importBatchHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sql = <<<'SQL'
            SELECT
                COUNT(*)
            FROM service_serials se
            LEFT JOIN service er ON se.parent_id = er.id
            WHERE er.sn != 'PLEASE CHANGE'
            SQL;

        /** @var ProcessHelper $helper */
        $helper = $this->getHelper('process');
        $this->importBatchHelper->runBatch($input, $output, ImportEquipmentSerialsCommand::getDefaultName(), (int) $this->legacyConnection->executeQuery($sql)->fetchOne(), $helper, 1000);

        return Command::SUCCESS;
    }
}
