<?php

declare(strict_types=1);

namespace LegacyBundle\Manager;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

class ModListManager
{
    public function __construct(
        private readonly Connection $legacyConnection,
    ) {
    }

    public function getDefaultCurrency(int $solId): ?string
    {
        $sql = <<<'SQL'
            SELECT value
            FROM mod_lists
            WHERE module = 'SOL'
              AND list_name = 'DCUR'
              AND list_key = ''
              AND parent_id = :solId
            LIMIT 1
            SQL;

        $result = $this->legacyConnection->fetchOne($sql, ['solId' => $solId], ['solId' => ParameterType::INTEGER]);

        return false !== $result ? $result : null;
    }
}
