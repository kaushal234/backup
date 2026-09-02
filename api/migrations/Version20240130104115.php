<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240130104115 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add all sub division from "ALVEST PARTS & ACCESSORIES" on FEATURE_PARTS_DASHBOARD_FULL_VIEW';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature_sub_division (sub_division_id, feature_id)
                        SELECT directory_sub_division.id, feature.id
                        FROM directory_sub_division, feature
                        WHERE feature.name = "FEATURE_PARTS_DASHBOARD_FULL_VIEW"
                        AND directory_sub_division.name IN ("SAGE PARTS", "ALVEST PARTS & ACCESSORIES", "ACCESSORIES")');
    }

    public function down(Schema $schema): void
    {
    }
}
