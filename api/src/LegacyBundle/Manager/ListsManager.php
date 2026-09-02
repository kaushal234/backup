<?php

declare(strict_types=1);

namespace LegacyBundle\Manager;

use Doctrine\DBAL\Connection;

class ListsManager
{
    private readonly Connection $legacyConnection;

    public function __construct(Connection $legacyConnection)
    {
        $this->legacyConnection = $legacyConnection;
    }

    public function getInternalTransactions(string $list_name): array
    {
        $queryBuilder = $this->legacyConnection->createQueryBuilder();

        $sql = <<<'SQL'
            SELECT list_item
            FROM lists
            WHERE list_name = :listName
            ORDER BY list_item
            SQL;
        $queryBuilder->setParameter('listName', $list_name);
        $results = $this->legacyConnection->fetchAllAssociative($sql, $queryBuilder->getParameters(), $queryBuilder->getParameterTypes());

        return array_column($results, 'list_item');
    }
}
