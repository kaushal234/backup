<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\EquipmentRecord;
use App\Entity\Finance\Currency;
use App\Entity\Finance\ManufacturingMargin;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use LegacyBundle\Command\Helper\SanitationHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:finance:manufacturing_margins')]
class ImportManufacturingMarginCommand extends Command
{
    private readonly ImportHelper $helper;
    private readonly Connection $legacyConnection;
    private readonly EntityCacheHelperFactory $cacheFactory;
    private readonly SanitationHelper $sanitationHelper;

    public function __construct(ImportHelper $helper, Connection $legacyConnection, EntityCacheHelperFactory $cacheFactory, SanitationHelper $sanitationHelper)
    {
        parent::__construct();
        $this->setDescription('Imports Manufacturing Margins from legacy');
        $this->helper = $helper;
        $this->legacyConnection = $legacyConnection;
        $this->cacheFactory = $cacheFactory;
        $this->sanitationHelper = $sanitationHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $currencyCache = $this->cacheFactory->createEntityCache(Currency::class, 'name');
        $equipmentRecordCache = $this->cacheFactory->createEntityCache(EquipmentRecord::class, 'legacyId');

        // Import manufacturing margins
        $sql = <<<'SQL'
            SELECT id, er_id, month, year, cur, factory_rev, std_hour, act_hour, std_lab_cost, act_lab_cost, std_mat, act_mat, std_other_mat, act_other_mat, std_other_dir_cost, act_other_dir_cost, comment
            FROM mfg_margins
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);

        $this->helper->progressiveImport(
            $output, $stmt, ManufacturingMargin::class, 'legacyId', 'id',
            function (ManufacturingMargin $manufacturingMargin, array $data) use ($currencyCache, $equipmentRecordCache) {
                $manufacturingMargin
                    ->setEquipmentRecord($equipmentRecordCache->fetch($data['er_id']))
                    ->setCurrency($currencyCache->fetch($data['cur']))
                    ->setExportedAt(new \DateTime(\sprintf('%d-%d', $data['year'], $data['month'])))
                    ->setFactoryRevenue((float) $data['factory_rev'])
                    ->setStandardHours((float) $data['std_hour'])
                    ->setActualHours((float) $data['act_hour'])
                    ->setStandardLabourCost((float) $data['std_lab_cost'])
                    ->setActualLabourCost((float) $data['act_lab_cost'])
                    ->setStandardMaterialCost((float) $data['std_mat'])
                    ->setActualMaterialCost((float) $data['act_mat'])
                    ->setStandardOtherMaterialCost((float) $data['std_other_mat'])
                    ->setActualOtherMaterialCost((float) $data['act_other_mat'])
                    ->setStandardOtherDirectCost((float) $data['std_other_dir_cost'])
                    ->setActualOtherDirectCost((float) $data['act_other_dir_cost'])
                    ->setComment(mb_trim($this->sanitationHelper->parse($data['comment'])))
                ;
            }
        );

        return 0;
    }
}
