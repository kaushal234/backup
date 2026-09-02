<?php

declare(strict_types=1);

namespace LegacyBundle\Command\EquipmentRecordGroup;

use App\Entity\EquipmentRecord;
use App\Entity\Sales\OrderTransaction;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'legacy:er:group:sync:sor_transactions',
    description: 'Imports sales order transactions'
)]
class SalesOrderTransactionSyncCommand extends Command
{
    public function __construct(
        private readonly ImportHelper $helper,
        private readonly Connection $legacyConnection,
        private readonly EntityCacheHelperFactory $cacheHelperFactory
    ) {
        parent::__construct();
    }

    public function skipFilter(OrderTransaction $orderTransaction, array $data): bool
    {
        return $orderTransaction->equipmentRecord->getLegacyId() === $data['id']
            && $orderTransaction->invoice === $data['nref']
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $logger = new ConsoleLogger($output);
        $equipmentRecordCache = $this->cacheHelperFactory->createEntityCache(EquipmentRecord::class, 'legacyId');

        $logger->info('Importing sor transaction');
        $sql = <<<'SQL'
            SELECT er.id, sor_tran.nref
            FROM service AS er
            LEFT JOIN sor_tran ON er.tranid_sso=sor_tran.id
            WHERE sor_tran.nref != ''
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);

        $this->helper->disableValidation();
        $this->helper->setBatchSize(5_000);

        $this->helper->progressiveImport(
            $output, $stmt, OrderTransaction::class, 'legacyId', 'id',
            static function (OrderTransaction $orderTransaction, array $data) use ($equipmentRecordCache) {
                $orderTransaction->equipmentRecord = $equipmentRecordCache->fetch((string) $data['id']);
                $orderTransaction->invoice = '' === $data['nref'] ? null : $data['nref'];
            }, true,
            $this->skipFilter(...)
        );

        return Command::SUCCESS;
    }
}
