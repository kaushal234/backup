<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250122131035 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'New feature for PowerBi report 51 & 52';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_POWERBI_FINANCE_BOOKING_READ")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_POWERBI_FINANCE_BOOKING_READ"
          AND user_group.name in (
            "ROLE_FC",
            "SUPERUSER",
            "ROLE_CFO",
            "ROLE_PLANNER",
            "ROLE_PM",
            "ROLE_PS",
            "ROLE_BYR",
            "ROLE_MLM",
            "ROLE_COO",
            "ROLE_PSM"
          )'
        );
    }

    public function down(Schema $schema): void
    {
    }
}
