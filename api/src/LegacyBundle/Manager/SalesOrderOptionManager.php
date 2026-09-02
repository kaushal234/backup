<?php

declare(strict_types=1);

namespace LegacyBundle\Manager;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;

class SalesOrderOptionManager
{
    public function __construct(
        private readonly Connection $legacyConnection,
        private readonly ListsManager $listsManager
    ) {
    }

    public function getTotalNegotiatedTransferPrices(int $id): array
    {
        $categories = $this->listsManager->getInternalTransactions('list.sol.caty.int');

        if ([] === $categories) {
            return [];
        }

        $sql = <<<'SQL'
            SELECT
                sol.id AS sol_id,
                sor_opts.pric_cur AS currency,
                SUM( sor_opts.pric ) AS negotiatedTransferPrice
            FROM
                sor_opts
            INNER JOIN
                sor_lines AS sol ON sol.id = sor_opts.parent_id
            WHERE
                sol.id = :id and caty IN (:categories)
            SQL;

        return $this->legacyConnection->fetchAssociative(
            $sql,
            [
                'id' => $id,
                'categories' => $categories,
            ],
            [
                'categories' => ArrayParameterType::STRING,
            ]
        );
    }
}
