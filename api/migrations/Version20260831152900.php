<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260831152900 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Allow TAXIBOT to access Parts Dashboard';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature_sub_division (sub_division_id, feature_id)
                        SELECT directory_sub_division.id, feature.id
                        FROM directory_sub_division, feature
                        WHERE feature.name = "FEATURE_PARTS_DASHBOARD_FULL_VIEW"
                        AND directory_sub_division.name IN ("TAXIBOT")');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE feature_sub_division
                        FROM feature_sub_division
                        INNER JOIN directory_sub_division ON directory_sub_division.id = feature_sub_division.sub_division_id
                        INNER JOIN feature ON feature.id = feature_sub_division.feature_id
                        WHERE feature.name = "FEATURE_PARTS_DASHBOARD_FULL_VIEW"
                        AND directory_sub_division.name IN ("TAXIBOT")');
    }
}
