<?php

declare(strict_types=1);

namespace LegacyBundle\Manager;

use App\Entity\Sales\Order;
use App\Entity\Sales\OrderLine;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;

class SalesOrderLineManager
{
    public function __construct(
        private readonly Connection $legacyConnection,
        private readonly ModListManager $modListManager,
    ) {
    }

    public function getSalesOrderLines(Order $order): array
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->where('parent_id = :sor')
            ->setParameter('sor', $order->getLegacyId(), ParameterType::INTEGER)
        ;

        $stmt = $this->legacyConnection->prepare($queryBuilder->getSQL());
        foreach ($queryBuilder->getParameters() as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        return $stmt->executeQuery()->fetchAllAssociative();
    }

    public function getSalesOrderLinesIDs(Order $order): array
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->select('sol.id')
            ->where('parent_id = :sor')
            ->andWhere('status NOT IN (:statuses) OR dt_opened < :startOfMonth')
            ->setParameter('sor', $order->getLegacyId(), ParameterType::INTEGER)
            ->setParameter('statuses', ['PENDING', 'CREATE_PO'], ArrayParameterType::STRING)
            ->setParameter('startOfMonth', (new \DateTime('midnight first day of this month'))->format('Y-m-d'))
        ;

        return $this->legacyConnection->fetchAllAssociative($queryBuilder->getSQL(), $queryBuilder->getParameters(), $queryBuilder->getParameterTypes());
    }

    public function isPreventingOrderClosing(Order $order): bool
    {
        return !empty($this->getOpenedSalesOrderLines($order));
    }

    public function getOpenedSalesOrderLines(Order $order): array
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->where('parent_id = :sor')
            ->andWhere('status != :status')
            ->setParameter('sor', $order->getLegacyId())
            ->setParameter('status', 'CLOSED')
        ;

        return $this->legacyConnection->fetchAllAssociative($queryBuilder->getSQL(), $queryBuilder->getParameters(), $queryBuilder->getParameterTypes());
    }

    public function duplicateSalesOrderLines(Order $source, Order $target)
    {
        if ([] === ($salesOrderLines = $this->getSalesOrderLines($source))) {
            return;
        }

        $targetId = $target->getLegacyId();

        $fieldsToDuplicate = [
            'bu', 'sls_orno', 'model', 'qty', 'del_pen', 'conf_sls', 'conf_erp', 'delpen_cond', 'wrty_spec', 'wrty_std', 'conf_wrty_erp', 'tpay', 'conf_cxo',
            'cu_ocur', 'dp_amt', 'dp_pc', 'parts_inc', 'docs_inc', 'conf_lc', 'notes', 'trans', 'inco', 'inco_loc', 'conf_cis', 'ctry',
        ];

        $queryBuilder = $this->legacyConnection->createQueryBuilder();
        foreach ($salesOrderLines as $salesOrder) {
            $sql = 'INSERT INTO sor_lines SET parent_id = :sor, dt_opened = NOW(), status = :status';
            $queryBuilder
                ->setParameter('sor', $targetId)
                ->setParameter('status', 'PENDING')
            ;

            foreach ($fieldsToDuplicate as $field) {
                $sql .= \sprintf(', %1$s = :%1$s', $field);
                $queryBuilder->setParameter($field, $salesOrder[$field] ?? '');
            }

            $stmt = $this->legacyConnection->prepare($sql);
            foreach ($queryBuilder->getParameters() as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->executeStatement();

            $solId = (int) $this->legacyConnection->lastInsertId();
            $id = (int) $salesOrder['id'];
            // Duplicate currencies and rates
            $sql = <<<'SQL'
                INSERT INTO mod_lists (parent_id, module, list_name, list_key, list_key2, value, value2)
                    SELECT :duplicata, module, list_name, list_key, list_key2, value, value2
                    FROM mod_lists WHERE module = 'SOL' AND parent_id = :origin
                SQL;
            $stmt = $this->legacyConnection->prepare($sql);
            $stmt->bindValue('origin', $id, ParameterType::INTEGER);
            $stmt->bindValue('duplicata', $solId, ParameterType::INTEGER);
            $stmt->executeStatement();

            // Duplicate breakdown
            $sql = <<<'SQL'
                INSERT INTO sor_opts (parent_id, caty, dsca, qty, mrsp_cur, mrsp, prip_cur, prip, pric_cur, pric, pris_cur, pris)
                    SELECT :duplicata, caty, dsca, qty, mrsp_cur, mrsp, prip_cur, prip, pric_cur, pric, pris_cur, pris
                    FROM sor_opts WHERE parent_id = :origin
                SQL;
            $stmt = $this->legacyConnection->prepare($sql);
            $stmt->bindValue('origin', $id, ParameterType::INTEGER);
            $stmt->bindValue('duplicata', $solId, ParameterType::INTEGER);
            $stmt->executeStatement();

            // Duplicate SOU
            $sql = <<<'SQL'
                INSERT INTO sor_units (parent_id, short_desc, long_desc, dgt_est, batch_qty, del_early)
                    SELECT :duplicata, short_desc, long_desc, dgt_est, batch_qty, "N"
                    FROM sor_units WHERE parent_id = :origin
                SQL;
            $stmt = $this->legacyConnection->prepare($sql);
            $stmt->bindValue('origin', $id, ParameterType::INTEGER);
            $stmt->bindValue('duplicata', $solId, ParameterType::INTEGER);
            $stmt->executeStatement();
        }
    }

    public function deleteSalesOrderLines(Order $order)
    {
        $salesOrderLines = $this->getOpenedSalesOrderLines($order);

        foreach ($salesOrderLines as $salesOrder) {
            // Seems the legacy notification is not necessary as only done when status in not 'PENDING' nor 'CREATE_PO'
            // which can't happen at SOR deletion as per OrderDeletionVoter

            // Delete SOL
            $id = (int) $salesOrder['id'];
            $stmt = $this->legacyConnection->prepare('DELETE FROM sor_lines WHERE id = :id');
            $stmt->bindValue('id', $id, ParameterType::INTEGER);
            $stmt->executeStatement();
            // Delete transactions
            $stmt = $this->legacyConnection->prepare('DELETE FROM sor_tran WHERE parent_id = :id');
            $stmt->bindValue('id', $id, ParameterType::INTEGER);
            $stmt->executeStatement();
            // Delete list items
            $stmt = $this->legacyConnection->prepare('DELETE FROM mod_lists WHERE module = :module AND parent_id = :id');
            $stmt->bindValue('id', $id, ParameterType::INTEGER);
            $stmt->bindValue('module', 'SOL');
            $stmt->executeStatement();
            // Delete breakdown
            $stmt = $this->legacyConnection->prepare('DELETE FROM sor_opts WHERE parent_id = :id');
            $stmt->bindValue('id', $id, ParameterType::INTEGER);
            $stmt->executeStatement();
            // Clean ER linked to units
            $sql = "UPDATE service SET sor_uid = '', buyer_customer_id = '', customer_id = '', customer_name = '', rrd_sso = '', rrd_erp = '' WHERE sor_uid IN (SELECT id FROM sor_units WHERE parent_id = :id)";
            $stmt = $this->legacyConnection->prepare($sql);
            $stmt->bindValue('id', $id, ParameterType::INTEGER);
            $stmt->executeStatement();
            // Delete units
            $stmt = $this->legacyConnection->prepare('DELETE FROM sor_units WHERE parent_id = :id');
            $stmt->bindValue('id', $id, ParameterType::INTEGER);
            $stmt->executeStatement();
        }
    }

    public function getLegacySalesOrderLine(OrderLine $orderLine): array
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->where('id = :id')
            ->setParameter('id', $orderLine->getLegacyId())
        ;

        return $this->legacyConnection->fetchAllAssociative($queryBuilder->getSQL(), $queryBuilder->getParameters(), $queryBuilder->getParameterTypes());
    }

    public function getUnitGrossSellingPrice(int $solId): int|float|string|null
    {
        $currency = $this->modListManager->getDefaultCurrency($solId);

        if (null === $currency) {
            return null;
        }

        if ('USD' === $currency) {
            $sql = <<<'SQL'
                SELECT
                    SUM(
                        IF(t1.pris_cur = 'USD',
                            t1.pris,
                            ROUND(pris / (SELECT value FROM mod_lists WHERE module = 'SOL' AND list_name = 'CURS' AND parent_id = t1.parent_id AND t1.pris_cur = list_key), 2)
                        )
                    ) AS unitGrossSellingPrice
                FROM sor_opts AS t1
                WHERE t1.parent_id = :id
                SQL;

            $result = $this->legacyConnection->fetchOne($sql, ['id' => $solId], ['id' => ParameterType::INTEGER]);
        } else {
            $sql = <<<'SQL'
                SELECT
                    SUM(
                        CASE
                            WHEN t1.pris_cur = 'USD' THEN
                                ROUND(pris * (SELECT value FROM mod_lists WHERE module = 'SOL' AND list_name = 'CURS' AND parent_id = t1.parent_id AND list_key = :currency), 2)
                            WHEN t1.pris_cur = :currency THEN
                                t1.pris
                            ELSE
                                ROUND(pris * (SELECT value FROM mod_lists WHERE module = 'SOL' AND list_name = 'CURS' AND parent_id = t1.parent_id AND list_key = :currency) /
                                (SELECT value FROM mod_lists WHERE module = 'SOL' AND list_name = 'CURS' AND parent_id = t1.parent_id AND list_key = t1.pris_cur), 2)
                        END
                    ) AS unitGrossSellingPrice
                FROM sor_opts AS t1
                WHERE t1.parent_id = :id
                SQL;

            $result = $this->legacyConnection->fetchOne($sql, ['id' => $solId, 'currency' => $currency], ['id' => ParameterType::INTEGER]);
        }

        return false !== $result ? $result : null;
    }

    protected function getQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->legacyConnection->createQueryBuilder();

        return $queryBuilder
            ->select('sol.*')
            ->from('sor_lines', 'sol');
    }
}
