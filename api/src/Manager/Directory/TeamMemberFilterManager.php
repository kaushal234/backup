<?php

declare(strict_types=1);

namespace App\Manager\Directory;

use Doctrine\DBAL\Connection;

class TeamMemberFilterManager
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function positionFilter(array $positions)
    {
        if ([] === $positions) {
            return '';
        }

        $expressionBuilder = $this->connection->createExpressionBuilder();

        return 'WHERE '.$expressionBuilder->in('ancestor.position_code', $positions);
    }

    public function order(array $orders)
    {
        $sql = 'ORDER BY';

        foreach ($orders as $field => $way) {
            $sql .= \sprintf(' %s %s', $field, mb_strtoupper($way));

            if ($field !== array_key_last($orders)) {
                $sql .= ',';
            }
        }

        return $sql;
    }
}
