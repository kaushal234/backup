<?php

declare(strict_types=1);

namespace LegacyBundle\Manager;

use Doctrine\DBAL\Connection;

class CommonManager
{
    public function __construct(
        private readonly Connection $legacyConnection,
    ) {
    }

    public function getSource(string $table, int $id, array $fields): array
    {
        $sql = \sprintf(
            'SELECT %s FROM %s WHERE id = :id',
            implode(',', $fields),
            $table
        );

        $result = $this->legacyConnection->fetchAssociative(
            $sql,
            [
                'table' => $table,
                'id' => $id,
                'fields' => implode(', "-"', $fields),
            ]
        );

        return \is_array($result) ? $result : [];
    }
}
