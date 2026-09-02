<?php

declare(strict_types=1);

namespace Application\Migrations;

use App\Doctrine\Migration\DoctrineMigrationHelperTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260618105030 extends AbstractMigration
{
    use DoctrineMigrationHelperTrait;

    private const POSITION_CODE = 'SEE';
    private const GROUP_NAME = 'ROLE_SEE';

    private const PICTOGRAM_ADMIN_FEATURES = [
        'FEATURE_PICTOGRAM_CREATE',
        'FEATURE_PICTOGRAM_UPDATE',
        'FEATURE_PICTOGRAM_DELETE',
        'FEATURE_PICTOGRAM_CATEGORY_CREATE',
        'FEATURE_PICTOGRAM_CATEGORY_UPDATE',
        'FEATURE_PICTOGRAM_CATEGORY_DELETE',
    ];

    public function getDescription(): string
    {
        return 'Grant pictogram admin (create/update/delete) to position SEE through ROLE_SEE';
    }

    public function up(Schema $schema): void
    {
        // 1. Attach the pictogram admin features to ROLE_SEE.
        //    The features already exist (currently held by SUPERUSER / role_GCTO),
        //    so we only insert the feature_group links (insertFeature = false).
        foreach (self::PICTOGRAM_ADMIN_FEATURES as $feature) {
            $this->insertFeatureGroup($feature, [self::GROUP_NAME], false);
        }

        // 2. Wire ROLE_SEE into every division group of position SEE so it becomes
        //    part of the position's standard permission set.
        $this->addSql(
            'INSERT IGNORE INTO division_group_group (division_group_id, group_id)
                SELECT dg.id, g.id
                FROM division_group dg
                INNER JOIN directory_position p ON p.id = dg.position_id
                CROSS JOIN user_group g
                WHERE p.code = :position AND g.name = :group',
            ['position' => self::POSITION_CODE, 'group' => self::GROUP_NAME],
        );
    }

    public function down(Schema $schema): void
    {
        // Detach ROLE_SEE from the SEE position division groups.
        $this->addSql(
            'DELETE dgg FROM division_group_group dgg
                INNER JOIN division_group dg ON dg.id = dgg.division_group_id
                INNER JOIN directory_position p ON p.id = dg.position_id
                INNER JOIN user_group g ON g.id = dgg.group_id
                WHERE p.code = :position AND g.name = :group',
            ['position' => self::POSITION_CODE, 'group' => self::GROUP_NAME],
        );

        // Remove the pictogram admin features from ROLE_SEE (the features themselves
        // are left untouched as they are still used by other groups).
        foreach (self::PICTOGRAM_ADMIN_FEATURES as $feature) {
            $this->addSql(
                'DELETE fg FROM feature_group fg
                    INNER JOIN user_group g ON g.id = fg.group_id
                    INNER JOIN feature f ON f.id = fg.feature_id
                    WHERE g.name = :group AND f.name = :feature',
                ['group' => self::GROUP_NAME, 'feature' => $feature],
            );
        }
    }
}
