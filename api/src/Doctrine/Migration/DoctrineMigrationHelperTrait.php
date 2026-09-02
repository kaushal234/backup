<?php

declare(strict_types=1);

namespace App\Doctrine\Migration;

trait DoctrineMigrationHelperTrait
{
    private function insertFeatureGroup(string $feature, array $groups, bool $insertFeature = true): void
    {
        if ($insertFeature) {
            $sql = 'INSERT IGNORE INTO feature (name) VALUES (:feature)';
            $this->addSql($sql, ['feature' => $feature]);
        }

        foreach ($groups as $group) {
            $sql = 'INSERT IGNORE INTO feature_group (group_id, feature_id)
                SELECT ug.id, f.id
                FROM user_group ug
                CROSS JOIN feature f
                WHERE ug.name = :group AND f.name = :feature';

            $this->addSql($sql, [
                'group' => $group,
                'feature' => $feature,
            ]);
        }
    }
}
