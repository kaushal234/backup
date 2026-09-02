<?php

declare(strict_types=1);

namespace LegacyBundle\Manager;

use App\Entity\Finance\ManufacturingMargin;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;

class ManufacturingMarginManager
{
    public function __construct(
        private readonly Connection $legacyConnection,
        private readonly ListsManager $listsManager,
    ) {
    }

    public function getSalesOrderLineInformation(ManufacturingMargin $manufacturingMargin): array
    {
        $categories = $this->listsManager->getInternalTransactions('list.sol.caty.int');
        $queryBuilder = $this->legacyConnection->createQueryBuilder();

        $sql = <<<'SQL'
            SELECT
                loc_sso.location AS sso_fullname,
                loc_erp.location AS erp_fullname,
                buyers.customer_name AS buyer,
                users.customer_name AS user_customer,
                sor_lines.ctry AS country,
                sor_lines.model AS model,
                sor_lines.id AS sol_id,
                sor_lines.factory_margin AS est_dir_margin_per,
                (CASE currency.currencyName
                    WHEN 'USD' THEN (
                        IF(discount_usd.published_transfer_price > discount_usd.negociated_transfer_price,
                    (discount_usd.published_transfer_price - discount_usd.negociated_transfer_price) * 100 / discount_usd.published_transfer_price, 0))
                    ELSE (
                        IF(discount_other_currency.published_transfer_price > discount_other_currency.negociated_transfer_price,
                    ((discount_other_currency.published_transfer_price - discount_other_currency.negociated_transfer_price) * 100 / discount_other_currency.published_transfer_price), 0))
                    END) AS factory_discount
            FROM mfg_margins
                LEFT JOIN service ON mfg_margins.er_id=service.id
                LEFT JOIN sor_units ON service.sor_uid=sor_units.id
                LEFT JOIN sor_lines ON sor_units.parent_id=sor_lines.id
                LEFT JOIN sor ON sor_lines.parent_id=sor.id
                LEFT JOIN customers buyers ON sor.buyer_customer_id=buyers.id
                LEFT JOIN customers users ON sor.user_customer_id=users.id
                LEFT JOIN locations AS loc_erp ON sor_lines.bu = loc_erp.id
                LEFT JOIN locations AS loc_sso ON sor.sso = loc_sso.id
                LEFT JOIN (
                            SELECT
                                value AS currencyName,
                                parent_id
                            FROM mod_lists
                            WHERE module='SOL' AND  list_name='DCUR'  AND  list_key=''
                            ORDER BY list_name, list_key, value LIMIT 1
                        ) AS currency ON currency.parent_id = sor_lines.id
                        LEFT JOIN (
                            SELECT
                                t1.parent_id AS parent_id,
                                SUM(IF(t1.mrsp_cur='USD',
                                t1.mrsp,
                                ROUND(mrsp/(SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND t1.mrsp_cur=list_key), 2)
                                )) AS published_transfer_price,
                                SUM(case WHEN t1.caty='SPECIAL DISCOUNT' THEN (if(t1.pric>0,IF(t1.pric_cur='USD',
                                t1.pric,
                                ROUND(pric/(SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND t1.pric_cur=list_key), 2)
                                ),0)) ELSE
                                IF(t1.pric_cur='USD',
                                t1.pric,
                                ROUND(pric/(SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND t1.pric_cur=list_key), 2)
                                ) END) AS negociated_transfer_price
                            FROM sor_opts AS t1
                            WHERE caty IN (:categories)
                            GROUP BY t1.parent_id
                        ) AS discount_usd ON discount_usd.parent_id = sor_lines.id
                        LEFT JOIN (
                            SELECT
                                t1.parent_id AS parent_id,
                                SUM(CASE WHEN t1.mrsp_cur='USD' THEN
                                ROUND(mrsp*(SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND list_key='EUR'),2)
                                WHEN t1.mrsp_cur='EUR' THEN
                                t1.mrsp
                                ELSE
                                ROUND(mrsp*(SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND list_key='EUR')/
                                (SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND list_key=t1.mrsp_cur), 2)
                                END
                                ) AS published_transfer_price,
                                SUM(CASE WHEN t1.pric_cur='USD' THEN
                                ROUND(IF((t1.caty = 'SPECIAL DISCOUNT' and t1.pric<0) ,0,pric)*(SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND list_key='EUR'), 2)
                                WHEN t1.pric_cur='EUR' THEN
                                t1.pric
                                ELSE
                                ROUND(IF((t1.caty = 'SPECIAL DISCOUNT') ,0,pric)*(SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND list_key='EUR')/
                                (SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND list_key=t1.pric_cur), 2)
                                END
                                ) AS negociated_transfer_price
                            FROM sor_opts AS t1
                            WHERE caty IN (:categories)
                            GROUP BY t1.parent_id
                        ) AS discount_other_currency ON discount_other_currency.parent_id = sor_lines.id
            WHERE mfg_margins.id = :legacyId
            LIMIT 1
            SQL;

        $queryBuilder->setParameter('categories', $categories, ArrayParameterType::STRING);
        $queryBuilder->setParameter('legacyId', $manufacturingMargin->getLegacyId());

        $results = $this->legacyConnection->fetchAllAssociative($sql, $queryBuilder->getParameters(), $queryBuilder->getParameterTypes());

        return $results[0] ?? [];
    }

    public function getSalesOrderLineInformationByFinanceFamily(string $dateFrom, string $dateTo, string $factory): array
    {
        $categories = $this->listsManager->getInternalTransactions('list.sol.caty.int');
        $queryBuilder = $this->legacyConnection->createQueryBuilder();

        $sql = <<<'SQL'
            SELECT
                models.finance_family AS financeFamily,
                (SUM(mfg_margins.factory_rev * sol.factory_margin) / SUM(mfg_margins.factory_rev)) AS average_projected_direct_margin,
                (SUM(mfg_margins.factory_rev * sol.factory_discount) / SUM(mfg_margins.factory_rev)) AS average_factory_discount
            FROM mfg_margins
                LEFT JOIN service ON mfg_margins.er_id = service.id
                LEFT JOIN models ON service.model = models.model
                LEFT JOIN (
                    SELECT
                        sor_lines.factory_margin AS factory_margin,
                        mfg_2.er_id,
                        sor_lines.id AS sol_id,
                        (CASE currency.currencyName
                            WHEN 'USD' THEN (
                                IF(discount_usd.published_transfer_price > discount_usd.negociated_transfer_price,
                            (discount_usd.published_transfer_price - discount_usd.negociated_transfer_price) * 100 / discount_usd.published_transfer_price, 0))
                            ELSE (
                                IF(discount_other_currency.published_transfer_price > discount_other_currency.negociated_transfer_price,
                            ((discount_other_currency.published_transfer_price - discount_other_currency.negociated_transfer_price) * 100 / discount_other_currency.published_transfer_price), 0))
                            END) AS factory_discount
                    FROM mfg_margins mfg_2
                        LEFT JOIN service ON mfg_2.er_id=service.id
                        LEFT JOIN sor_units ON service.sor_uid=sor_units.id
                        LEFT JOIN sor_lines ON sor_units.parent_id=sor_lines.id
                        LEFT JOIN (
                            SELECT
                                value AS currencyName,
                                parent_id
                            FROM mod_lists
                            WHERE module='SOL' AND  list_name='DCUR'  AND  list_key=''
                            ORDER BY list_name, list_key, value LIMIT 1
                        ) AS currency ON currency.parent_id = sor_lines.id
                        LEFT JOIN (
                            SELECT
                                t1.parent_id AS parent_id,
                                SUM(IF(t1.mrsp_cur='USD',
                                t1.mrsp,
                                ROUND(mrsp/(SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND t1.mrsp_cur=list_key), 2)
                                )) AS published_transfer_price,
                                SUM(case WHEN t1.caty='SPECIAL DISCOUNT' THEN (if(t1.pric>0,IF(t1.pric_cur='USD',
                                t1.pric,
                                ROUND(pric/(SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND t1.pric_cur=list_key), 2)
                                ),0)) ELSE
                                IF(t1.pric_cur='USD',
                                t1.pric,
                                ROUND(pric/(SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND t1.pric_cur=list_key), 2)
                                ) END) AS negociated_transfer_price
                            FROM sor_opts AS t1
                            WHERE caty IN (:categories)
                            GROUP BY t1.parent_id
                        ) AS discount_usd ON discount_usd.parent_id = sor_lines.id
                        LEFT JOIN (
                            SELECT
                                t1.parent_id AS parent_id,
                                SUM(CASE WHEN t1.mrsp_cur='USD' THEN
                                ROUND(mrsp*(SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND list_key='EUR'),2)
                                WHEN t1.mrsp_cur='EUR' THEN
                                t1.mrsp
                                ELSE
                                ROUND(mrsp*(SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND list_key='EUR')/
                                (SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND list_key=t1.mrsp_cur), 2)
                                END
                                ) AS published_transfer_price,
                                SUM(CASE WHEN t1.pric_cur='USD' THEN
                                ROUND(IF((t1.caty = 'SPECIAL DISCOUNT' and t1.pric<0) ,0,pric)*(SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND list_key='EUR'), 2)
                                WHEN t1.pric_cur='EUR' THEN
                                t1.pric
                                ELSE
                                ROUND(IF((t1.caty = 'SPECIAL DISCOUNT') ,0,pric)*(SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND list_key='EUR')/
                                (SELECT value FROM mod_lists WHERE module='SOL' AND list_name='CURS' AND parent_id=t1.parent_id AND list_key=t1.pric_cur), 2)
                                END
                                ) AS negociated_transfer_price
                            FROM sor_opts AS t1
                            WHERE caty IN (:categories)
                            GROUP BY t1.parent_id
                        ) AS discount_other_currency ON discount_other_currency.parent_id = sor_lines.id
                ) sol ON sol.er_id = mfg_margins.er_id
            WHERE service.man_location = :factory
                AND CAST(CONCAT(mfg_margins.year, '-', mfg_margins.month, '-01') AS DATE) <= :dateTo
                AND CAST(CONCAT(mfg_margins.year, '-', mfg_margins.month, '-01') AS DATE) >= :dateFrom
                AND models.finance_family IS NOT NULL
            GROUP BY models.finance_family
            SQL;

        $queryBuilder->setParameter('factory', $factory);
        $queryBuilder->setParameter('dateFrom', $dateFrom);
        $queryBuilder->setParameter('dateTo', $dateTo);
        $queryBuilder->setParameter('categories', $categories, ArrayParameterType::STRING);

        return $this->legacyConnection->fetchAllAssociative($sql, $queryBuilder->getParameters(), $queryBuilder->getParameterTypes());
    }
}
