<?php

declare(strict_types=1);

namespace LegacyBundle\Command\EquipmentRecordGroup;

use App\Entity\Directory\Location;
use App\Entity\Sales\Incoterm;
use App\Entity\Sales\Order;
use App\Entity\Sales\OrderLine;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use LegacyBundle\Command\Helper\SanitationHelper;
use LegacyBundle\Manager\SalesOrderLineManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'legacy:er:group:sync:sor_lines',
    description: 'Imports Sales order lines'
)]
class SalesOrderLineSyncCommand extends Command
{
    public function __construct(
        private readonly ImportHelper $helper,
        private readonly Connection $legacyConnection,
        private readonly EntityCacheHelperFactory $cacheHelperFactory,
        private readonly SanitationHelper $sanitationHelper,
        private readonly EntityManagerInterface $entityManager,
        private readonly SalesOrderLineManager $salesOrderLineManager,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct();
    }

    public function skipFilter(OrderLine $orderLine, array $data): bool
    {
        return (null === $orderLine->order || $orderLine->order->getLegacyId() === $data['parent_id'])
            && $orderLine->factory?->getLegacyId() === $data['bu']
            && $orderLine->incotermLocation === $data['inco_loc']
            && $orderLine->deliveryPenaltiesConditions === $data['delpen_cond']
            && $orderLine->incoterm?->code === $this->sanitationHelper->trimAndNullify($data['inco'])
            && $orderLine->status === $data['status']
            && ((null !== $orderLine->purchaseOrderAcceptedDate ? $orderLine->purchaseOrderAcceptedDate->format('Y-m-d') : null) === $data['dpo_ack'])
            && ($orderLine->inspection ? 'Y' === $data['conf_cis'] : \in_array($data['conf_cis'], ['N', ''], true))
            && ($orderLine->deliveryPenalties ? 'Y' === $data['del_pen'] : \in_array($data['del_pen'], ['N', ''], true))
            && ($orderLine->shipWithParts ? 'Y' === $data['parts_inc'] : \in_array($data['parts_inc'], ['N', ''], true))
            && ($orderLine->isPaymentTermValid ? 'Y' === $data['conf_cxo'] : 'N' === $data['conf_cxo'])
            && $orderLine->paymentTerms === $data['tpay']
            && (null === $orderLine->deliveredEarly ? '' === $data['del_early'] : ($orderLine->deliveredEarly ? 'Y' === $data['del_early'] : 'N' === $data['del_early']))
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $salesOrderLineRepository = $this->entityManager->getRepository(OrderLine::class);
        $deletedOrderLinesCount = 0;

        // First, delete SOLs that no longer exist in legacy
        foreach ($salesOrderLineRepository->findAll() as $orderLine) {
            $legacySalesOrderLine = $this->salesOrderLineManager->getLegacySalesOrderLine($orderLine);
            if (empty($legacySalesOrderLine)) {
                ++$deletedOrderLinesCount;
                foreach ($orderLine->getFactoryOrders() as $factoryOrder) {
                    $this->entityManager->remove($factoryOrder);
                    $this->logger->info(
                        \sprintf(
                            'OrderToFactory #%s deleted because the linked OrderLine (#%s) has to be deleted',
                            $factoryOrder->getId(),
                            $orderLine->getId()
                        )
                    );
                }
                $this->entityManager->remove($orderLine);
                $this->logger->info(
                    \sprintf(
                        "OrderLine #%s (legacyId %s) deleted because it doesn't exist in legacy database anymore",
                        $orderLine->getId(),
                        $orderLine->getLegacyId()
                    )
                );
            }
        }

        $output->writeln(\sprintf('%s OrderLines deleted in API database', $deletedOrderLinesCount));

        // Then Sync data from Legacy to API
        $logger = new ConsoleLogger($output);
        $orderCache = $this->cacheHelperFactory->createEntityCache(Order::class, 'legacyId');
        $incotermCache = $this->cacheHelperFactory->createEntityCache(Incoterm::class, 'code');
        $locationCache = $this->cacheHelperFactory->createEntityCache(Location::class, 'legacyId');

        $logger->info('Importing sor lines');
        $sql = <<<'SQL'
            SELECT sor_lines.id, sor_lines.parent_id, sor_lines.inco_loc, sor_lines.inco, sor_lines.conf_cis, sor_lines.conf_cxo, sor_lines.status, sor_lines.bu, sor_lines.parts_inc, sor_lines.tpay, sor_lines.del_pen, sor_lines.delpen_cond, MIN(sor_units.del_early) as del_early,
            (SELECT MIN(date) FROM mod_logs
                WHERE module='SOL' AND parent_id=sor_lines.id
                AND comment LIKE '%PRINT_FACTORY_SO_ACK%'
            ) as dpo_ack
            FROM service AS er
                LEFT JOIN sor_units ON er.sor_uid = sor_units.id
                LEFT JOIN sor_lines ON sor_units.parent_id = sor_lines.id
            WHERE sor_lines.id is not null
            GROUP BY sor_lines.id
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);

        $this->helper->disableValidation();
        $this->helper->setBatchSize(5_000);

        $this->helper->progressiveImport(
            $output, $stmt, OrderLine::class, 'legacyId', 'id',
            static function (OrderLine $orderLine, array $data) use ($orderCache, $incotermCache, $locationCache) {
                if (null === $orderCache->fetch((string) $data['parent_id'])) {
                    throw new \InvalidArgumentException('Order not found in API');
                }
                $orderLine->factory = $locationCache->fetch((string) $data['bu']);
                $orderLine->order = $orderCache->fetch((string) $data['parent_id']);
                $orderLine->incotermLocation = $data['inco_loc'];
                $orderLine->incoterm = $incotermCache->fetch((string) $data['inco']);
                if ('' !== $data['conf_cis']) {
                    $orderLine->inspection = !('N' === $data['conf_cis']);
                }
                if ('' !== $data['parts_inc']) {
                    $orderLine->shipWithParts = !('N' === $data['parts_inc']);
                }
                if ('' !== $data['del_pen']) {
                    $orderLine->deliveryPenalties = !('N' === $data['del_pen']);
                }
                if ('Y' === $data['del_early']) {
                    $orderLine->deliveredEarly = true;
                } elseif ('N' === $data['del_early']) {
                    $orderLine->deliveredEarly = false;
                } else {
                    $orderLine->deliveredEarly = null;
                }
                $orderLine->deliveryPenaltiesConditions = $data['delpen_cond'];
                $orderLine->purchaseOrderAcceptedDate = null === $data['dpo_ack'] ? null : new \DateTime($data['dpo_ack']);
                $orderLine->isPaymentTermValid = !('N' === $data['conf_cxo']);
                $orderLine->paymentTerms = $data['tpay'];
                $orderLine->status = $data['status'];
            }, true,
            $this->skipFilter(...)
        );

        return Command::SUCCESS;
    }
}
