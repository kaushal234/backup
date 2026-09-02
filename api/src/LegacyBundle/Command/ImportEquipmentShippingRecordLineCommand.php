<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\EquipmentRecord;
use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecord;
use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecordLine;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:esr:line')]
class ImportEquipmentShippingRecordLineCommand extends Command
{
    private readonly ImportHelper $helper;
    private readonly Connection $legacyConnection;
    private readonly EntityCacheHelperFactory $cacheHelperFactory;

    public function __construct(ImportHelper $helper, Connection $legacyConnection, EntityCacheHelperFactory $cacheHelperFactory)
    {
        parent::__construct();
        $this->setDescription('Imports equipment shipping record line from legacy esrl table');
        $this->helper = $helper;
        $this->legacyConnection = $legacyConnection;
        $this->cacheHelperFactory = $cacheHelperFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $logger = new ConsoleLogger($output);
        $equipmentRecordCache = $this->cacheHelperFactory->createEntityCache(EquipmentRecord::class, 'legacyId');
        $equipmentShippingRecordCache = $this->cacheHelperFactory->createEntityCache(EquipmentShippingRecord::class, 'legacyId');

        $logger->info('Importing esrl');
        $sql = <<<'SQL'
            SELECT esrl.id, esrl.parent_id, esrl.erid, esrl.dt_shipped, esrl.dt_estimated, esrl.dt_arrived, esrl.dt_pick_up
            FROM esrl
            INNER JOIN esr AS esr ON esr.id = esrl.parent_id
            WHERE esrl.parent_id IN (SELECT esr.id FROM esr)
            AND esr.cuid IN (SELECT customers.id FROM customers)
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);

        $this->helper->disableValidation();
        $this->helper->setBatchSize(5_000);

        $this->helper->progressiveImport(
            $output, $stmt, EquipmentShippingRecordLine::class, 'legacyId', 'id',
            static function (EquipmentShippingRecordLine $equipmentShippingRecordLine, array $data) use ($equipmentRecordCache, $equipmentShippingRecordCache) {
                $equipmentShippingRecordLine->equipmentRecord = $equipmentRecordCache->fetch((string) $data['erid']);
                $equipmentShippingRecordLine->equipmentShippingRecord = $equipmentShippingRecordCache->fetch((string) $data['parent_id']);
                $equipmentShippingRecordLine->vesselLoadingDate = '0000-00-00' === $data['dt_shipped'] ? null : new \DateTime($data['dt_shipped']);
                $equipmentShippingRecordLine->estimatedArrivalDate = '0000-00-00' === $data['dt_estimated'] ? null : new \DateTime($data['dt_estimated']);
                $equipmentShippingRecordLine->actualArrivalDate = '0000-00-00' === $data['dt_arrived'] ? null : new \DateTime($data['dt_arrived']);
                $equipmentShippingRecordLine->estimatedPickUpDate = '0000-00-00' === $data['dt_pick_up'] ? null : new \DateTime($data['dt_pick_up']);
            }
        );

        return Command::SUCCESS;
    }
}
