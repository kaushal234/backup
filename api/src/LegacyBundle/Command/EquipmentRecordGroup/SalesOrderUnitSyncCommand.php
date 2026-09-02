<?php

declare(strict_types=1);

namespace LegacyBundle\Command\EquipmentRecordGroup;

use App\Entity\EquipmentRecord;
use App\Entity\Sales\OrderLine;
use App\Entity\Sales\OrderToFactory;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'legacy:er:group:sync:sor_units',
    description: 'Imports Sales order units'
)]
class SalesOrderUnitSyncCommand extends Command
{
    public function __construct(
        private readonly ImportHelper $helper,
        private readonly Connection $legacyConnection,
        private readonly EntityCacheHelperFactory $cacheHelperFactory
    ) {
        parent::__construct();
    }

    public function skipFilter(OrderToFactory $orderToFactory, array $data): bool
    {
        return $orderToFactory->equipmentRecord?->getLegacyId() === $data['erId']
            && $orderToFactory->orderLine->getLegacyId() === $data['parent_id']
            && ((null !== $orderToFactory->requestedDeliveryDate ? $orderToFactory->requestedDeliveryDate->format('Y-m-d') : null) === $data['del_dat'])
            && ((null !== $orderToFactory->factoryPromisedDeliveryDate ? $orderToFactory->factoryPromisedDeliveryDate->format('Y-m-d') : null) === $data['ddel_est1'])
            && ((bool) $data['commissioning'] === $orderToFactory->commissioning)
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $logger = new ConsoleLogger($output);
        $equipmentRecordCache = $this->cacheHelperFactory->createEntityCache(EquipmentRecord::class, 'legacyId');
        $orderLineCache = $this->cacheHelperFactory->createEntityCache(OrderLine::class, 'legacyId');

        $logger->info('Importing sor unit');
        $sql = <<<'SQL'
            SELECT
                sor_units.commissioning,
                IF(sor_units.del_dat = '0000-00-00 00:00:00', NULL, sor_units.del_dat) as del_dat,
                IF(sor_units.ddel_est1 = '0000-00-00 00:00:00', NULL, sor_units.ddel_est1) as ddel_est1,
                sor_units.id,
                sor_units.parent_id,
                er.id AS erId
            FROM service AS er
            RIGHT JOIN sor_units ON er.sor_uid = sor_units.id
            WHERE er.id is not null
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);

        $this->helper->disableValidation();
        $this->helper->setBatchSize(5_000);

        $this->helper->progressiveImport(
            $output, $stmt, OrderToFactory::class, 'legacyId', 'id',
            static function (OrderToFactory $factoryOrder, array $data) use ($equipmentRecordCache, $orderLineCache) {
                if (null === ($orderLine = $orderLineCache->fetch((string) $data['parent_id'])) || null === ($equipmentRecord = $equipmentRecordCache->fetch((string) $data['erId']))) {
                    throw new \InvalidArgumentException('Order Line or Equipment Record not found in API');
                }
                $factoryOrder->orderLine = $orderLine;
                $factoryOrder->commissioning = (bool) $data['commissioning'];
                $factoryOrder->requestedDeliveryDate = null === $data['del_dat'] ? null : new \DateTime($data['del_dat']);
                $factoryOrder->factoryPromisedDeliveryDate = null === $data['ddel_est1'] ? null : new \DateTime($data['ddel_est1']);
                $factoryOrder->equipmentRecord = $equipmentRecord;
            }, true,
            $this->skipFilter(...)
        );

        return Command::SUCCESS;
    }
}
