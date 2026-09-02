<?php

declare(strict_types=1);

namespace App\Manager\Directory;

use Doctrine\DBAL\Connection;

class TeamMemberQueryManager
{
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    public function getSubordinateHierarchy(int $userId, string $conditions = '', string $order = ''): array
    {
        $cte = <<<SQL
                WITH RECURSIVE ancestor (id, firstname, lastname, supervisor, disabled, level, supervisor_id, position_code) AS (
                SELECT user.id, user.firstname, user.lastname, user.supervisor_id, user.disabled, 1 AS level, supervisor.id, directory_position.code
                FROM user
                         LEFT JOIN user supervisor ON user.supervisor_id = supervisor.id
                         LEFT JOIN directory_position ON user.position_id = directory_position.id
                WHERE user.id = {$userId}
                  AND user.disabled = 0
                  AND user.discr = 'people'
                UNION ALL
                SELECT e.id, e.firstname, e.lastname, e.supervisor_id, e.disabled, a.level + 1 AS level, supervisor.id, directory_position.code
                FROM user e
                         JOIN user supervisor ON e.supervisor_id = supervisor.id
                         JOIN ancestor a ON e.supervisor_id = a.id
                         LEFT JOIN directory_position ON e.position_id = directory_position.id
                WHERE e.disabled = 0
                  AND e.discr = 'people'
                )
                SELECT *
                FROM ancestor
                {$conditions}
                {$order};
            SQL;

        return $this->connection->fetchAllAssociative($cte);
    }

    public function getSupervisorHierarchy(int $userId, $conditions = ''): array
    {
        $cte = <<<SQL
                WITH RECURSIVE ancestor (id, firstname, lastname, supervisor, disabled, level, position_code) AS (
                SELECT user.id, user.firstname, user.lastname, user.supervisor_id, user.disabled, 1 AS level, directory_position.code
                FROM user
                     LEFT JOIN directory_position ON user.position_id = directory_position.id
                WHERE user.id = {$userId}
                  AND user.disabled = 0
                  AND user.discr = 'people'
                UNION ALL
                SELECT e.id, e.firstname, e.lastname, e.supervisor_id, e.disabled, a.level + 1 AS level, directory_position.code
                FROM user e
                         JOIN ancestor a ON e.id = a.supervisor
                         JOIN directory_position ON e.position_id = directory_position.id
                WHERE e.disabled = 0
                  AND e.discr = 'people'
                )
                SELECT *
                FROM ancestor
                {$conditions}
            SQL;

        return $this->connection->fetchAllAssociative($cte);
    }
}
