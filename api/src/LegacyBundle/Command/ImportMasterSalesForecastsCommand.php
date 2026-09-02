<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Sales\MasterSalesForecast;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\ImportHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:sales:master_sfr')]
class ImportMasterSalesForecastsCommand extends Command
{
    private readonly ImportHelper $helper;

    private readonly Connection $legacyConnection;

    public function __construct(ImportHelper $helper, Connection $legacyConnection)
    {
        parent::__construct();
        $this->setDescription('Imports Master SFR from legacy');
        $this->helper = $helper;
        $this->legacyConnection = $legacyConnection;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Import groups
        $sql = <<<'SQL'
            SELECT id
            FROM sfr_master
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);
        $this->helper->progressiveImport(
            $output, $stmt, MasterSalesForecast::class, 'legacyId', 'id',
            static function (MasterSalesForecast $masterSalesForecast, array $data) {
                $masterSalesForecast
                    ->setLegacyId((int) $data['id']);
            }, true
        );

        return 0;
    }
}
