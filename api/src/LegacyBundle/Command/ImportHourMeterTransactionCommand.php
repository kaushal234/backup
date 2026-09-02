<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\EquipmentRecord;
use App\Entity\Service\TechnicianOnCall;
use App\Entity\Support\EquipmentRecord\HourMeterTransaction\CustomerServiceRecordHourMeterTransaction;
use App\Entity\Support\EquipmentRecord\HourMeterTransaction\EquipmentRecordHourMeterTransaction;
use App\Entity\Support\EquipmentRecord\HourMeterTransaction\HourMeterTransaction;
use App\Entity\Support\EquipmentRecord\HourMeterTransaction\ServiceContractMaintenanceHourMeterTransaction;
use App\Entity\Support\EquipmentRecord\HourMeterTransaction\TechnicianOnCallHourMeterTransaction;
use App\Entity\Support\EquipmentRecord\HourMeterTransaction\WarrantyClaimHourMeterTransaction;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:support:hour_meter')]
class ImportHourMeterTransactionCommand extends Command
{
    public function __construct(
        private readonly ImportHelper $helper,
        private readonly Connection $legacyConnection,
        private readonly EntityCacheHelperFactory $cacheFactory,
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
        $this->setDescription('Imports Hour Meter transactions from legacy');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $equipmentRecordCache = $this->cacheFactory->createEntityCache(EquipmentRecord::class, 'legacyId');

        $this->helper->setBatchSize(10_000);

        $entities = [
            'CSR' => CustomerServiceRecordHourMeterTransaction::class,
            'ER' => EquipmentRecordHourMeterTransaction::class,
            TechnicianOnCall::MODULE_NAME => TechnicianOnCallHourMeterTransaction::class,
            'WC' => WarrantyClaimHourMeterTransaction::class,
            'SCM' => ServiceContractMaintenanceHourMeterTransaction::class,
        ];

        foreach ($entities as $module => $class) {
            // Import Hour Meter Transactions
            $sql = <<<'SQL'
                 SELECT id, parent_id, dt, hourmeter, module, module_id
                 FROM service_hourmeter
                 WHERE module = :module AND parent_id != 0
                SQL;
            $stmt = $this->legacyConnection->executeQuery($sql, ['module' => $module]);

            $this->helper->disableValidation();
            $this->helper->progressiveImport(
                $output, $stmt, $class, 'id', 'id',
                static function (HourMeterTransaction $hourMeterTransaction, array $data) use ($equipmentRecordCache, $output) {
                    if (null === ($equipmentRecord = $equipmentRecordCache->fetch((string) $data['parent_id']))) {
                        $output->writeln(\sprintf('<error>Hour Meter Transaction#%s not imported because ER not found</error>', $data['id']));
                        throw new \InvalidArgumentException('ER not found');
                    }

                    if ($hourMeterTransaction instanceof WarrantyClaimHourMeterTransaction) {
                        $hourMeterTransaction->warrantyClaimLegacyId = (int) $data['module_id'];
                    }

                    if ($hourMeterTransaction instanceof TechnicianOnCallHourMeterTransaction) {
                        $hourMeterTransaction->tocLegacyId = (int) $data['module_id'];
                    }

                    if ($hourMeterTransaction instanceof CustomerServiceRecordHourMeterTransaction) {
                        $hourMeterTransaction->setCustomerServiceRecordLegacyId((int) $data['module_id']);
                    }

                    $hourMeterTransaction->createdAt = null !== $data['dt'] ? new \DateTime($data['dt']) : new \DateTime();
                    $hourMeterTransaction->hourMeter = (int) $data['hourmeter'];
                    $hourMeterTransaction->setLegacyId((int) $data['id']);
                    $hourMeterTransaction->setEquipmentRecord($equipmentRecord);
                }
            );
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
