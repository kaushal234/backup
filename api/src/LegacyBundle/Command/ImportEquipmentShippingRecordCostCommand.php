<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Directory\People;
use App\Entity\Finance\Currency;
use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecord;
use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecordCost;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use LegacyBundle\Command\Helper\SanitationHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:esr:cost', description: 'Imports equipment shipping record cost from legacy mod_costs table')]
class ImportEquipmentShippingRecordCostCommand extends Command
{
    private readonly ImportHelper $helper;
    private readonly Connection $legacyConnection;
    private readonly EntityCacheHelperFactory $cacheHelperFactory;
    private SanitationHelper $sanitationHelper;

    public function __construct(ImportHelper $helper, Connection $legacyConnection, SanitationHelper $sanitationHelper, EntityCacheHelperFactory $cacheHelperFactory)
    {
        parent::__construct();
        $this->helper = $helper;
        $this->legacyConnection = $legacyConnection;
        $this->cacheHelperFactory = $cacheHelperFactory;
        $this->sanitationHelper = $sanitationHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $logger = new ConsoleLogger($output);
        $peopleCache = $this->cacheHelperFactory->createEntityCache(People::class, 'legacyId');
        $currencyCache = $this->cacheHelperFactory->createEntityCache(Currency::class, 'name');

        $equipmentShippingRecordCache = $this->cacheHelperFactory->createEntityCache(EquipmentShippingRecord::class, 'legacyId');

        $logger->info('Importing esr cost');
        $sql = <<<'SQL'
            SELECT mod_costs.id, mod_costs.poster, mod_costs.date_open, mod_costs.type, mod_costs.cur, mod_costs.description, mod_costs.parent_id, mod_costs.price
            FROM mod_costs
            INNER JOIN esr AS esr ON esr.id = mod_costs.parent_id
            WHERE mod_costs.module = 'ESR'
            AND mod_costs.parent_id IN (SELECT esr.id FROM esr)
            AND esr.cuid IN (SELECT customers.id FROM customers)
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);
        $this->helper->disableValidation();
        $this->helper->setBatchSize(5_000);

        $this->helper->progressiveImport(
            $output, $stmt, EquipmentShippingRecordCost::class, 'legacyId', 'id',
            function (EquipmentShippingRecordCost $equipmentShippingRecordCost, array $data) use ($equipmentShippingRecordCache, $peopleCache, $currencyCache) {
                $equipmentShippingRecordCost->createdBy = $peopleCache->fetch((string) $data['poster']);
                $equipmentShippingRecordCost->costDate = new \DateTime($data['date_open']);
                $equipmentShippingRecordCost->type = $data['type'];
                $equipmentShippingRecordCost->currency = $currencyCache->fetch($data['cur']);
                $equipmentShippingRecordCost->description = '' === $data['description'] ? null : mb_trim($this->sanitationHelper->decodeChinese($this->sanitationHelper->parse($data['description'])));
                $equipmentShippingRecordCost->equipmentShippingRecord = $equipmentShippingRecordCache->fetch((string) $data['parent_id']);
                $equipmentShippingRecordCost->price = (float) $data['price'];
            }
        );

        return Command::SUCCESS;
    }
}
